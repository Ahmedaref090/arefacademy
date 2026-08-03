<?php

namespace App\Services;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class FawryPaymentService
{
    protected string $merchantCode;
    protected string $securityKey;
    protected string $baseUrl;

    public function __construct()
    {
        $this->merchantCode = (string) config('fawry.merchant_code');
        $this->securityKey = (string) config('fawry.security_key');
        $this->baseUrl = rtrim((string) config('fawry.base_url'), '/');
    }

    /**
     * Create a pending enrollment + payment, then ask Fawry for a
     * PayAtFawry reference number the student pays at any Fawry outlet.
     */
    public function createChargeRequest(User $user, Course $course): Payment
    {
        $payment = DB::transaction(function () use ($user, $course) {
            $enrollment = Enrollment::firstOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['status' => EnrollmentStatus::Pending]
            );

            return Payment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
                'merchant_ref_number' => $this->generateMerchantRefNumber(),
                'amount' => $course->price,
                'status' => PaymentStatus::Pending,
                'expires_at' => now()->addHours((int) config('fawry.expiry_hours')),
            ]);
        });

        $response = $this->sendChargeRequest($user, $course, $payment);

        $payment->update([
            'fawry_reference_number' => $response['referenceNumber'] ?? null,
            'fawry_response' => $response,
        ]);

        return $payment->fresh();
    }

    protected function generateMerchantRefNumber(): string
    {
        return 'AREF-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
    }

    protected function sendChargeRequest(User $user, Course $course, Payment $payment): array
    {
        $amount = number_format((float) $payment->amount, 2, '.', '');
        $profileId = (string) $user->id;
        $method = 'PAYATFAWRY';

        $payload = [
            'merchantCode' => $this->merchantCode,
            'merchantRefNum' => $payment->merchant_ref_number,
            'customerProfileId' => $profileId,
            'customerName' => $user->name,
            'customerMobile' => $user->phone,
            'customerEmail' => $user->email ?: 'students@aref.academy',
            'paymentMethod' => $method,
            'amount' => $amount,
            'currencyCode' => config('fawry.currency'),
            'language' => 'en-gb',
            'chargeItems' => [[
                'itemId' => (string) $course->id,
                'description' => $course->title,
                'price' => $amount,
                'quantity' => 1,
            ]],
            'paymentExpiry' => $payment->expires_at->getTimestampMs(),
            'signature' => $this->chargeSignature($payment->merchant_ref_number, $profileId, $method, $amount),
        ];

        $response = Http::acceptJson()
            ->timeout(30)
            ->post($this->baseUrl . '/fawrypay-api/api/payments/init', $payload);

        if ($response->failed()) {
            throw new RuntimeException('Fawry charge request failed: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * Fawry v2 charge signature:
     * sha256(merchantCode + merchantRefNum + customerProfileId + paymentMethod + amount + secureKey)
     */
    protected function chargeSignature(string $refNum, string $profileId, string $method, string $amount): string
    {
        return hash('sha256', $this->merchantCode . $refNum . $profileId . $method . $amount . $this->securityKey);
    }

    /**
     * Verify the webhook callback signature.
     * sha256(fawryRefNumber + merchantRefNum + paymentAmount + orderAmount
     *        + orderStatus + paymentMethod + paymentRefrenceNumber? + secureKey)
     * Confirm the exact field order against your Fawry integration docs.
     */
    public function verifyWebhookSignature(array $payload): bool
    {
        $received = (string) ($payload['messageSignature'] ?? '');

        if ($received === '') {
            return false;
        }

        $raw = ($payload['fawryRefNumber'] ?? '')
            . ($payload['merchantRefNum'] ?? '')
            . ($payload['paymentAmount'] ?? '')
            . ($payload['orderAmount'] ?? '')
            . ($payload['orderStatus'] ?? '')
            . ($payload['paymentMethod'] ?? '')
            . ($payload['paymentRefrenceNumber'] ?? '')
            . $this->securityKey;

        return hash_equals(hash('sha256', $raw), $received);
    }
}
