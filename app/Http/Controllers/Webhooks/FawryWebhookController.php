<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\FawryPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FawryWebhookController extends Controller
{
    public function __invoke(Request $request, FawryPaymentService $fawry)
    {
        $payload = $request->all();

        if (! $fawry->verifyWebhookSignature($payload)) {
            Log::warning('Fawry webhook: invalid signature', $payload);

            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $payment = Payment::where('merchant_ref_number', $payload['merchantRefNum'] ?? '')->first();

        if (! $payment) {
            Log::warning('Fawry webhook: payment not found', $payload);

            return response()->json(['message' => 'Payment not found'], 404);
        }

        $orderStatus = strtoupper((string) ($payload['orderStatus'] ?? ''));

        DB::transaction(function () use ($payment, $orderStatus, $payload) {
            match ($orderStatus) {
                'PAID' => $this->markAsPaid($payment, $payload),
                'UNPAID' => $payment->update(['status' => PaymentStatus::Unpaid, 'fawry_response' => $payload]),
                'EXPIRED' => $this->markAsExpired($payment, $payload),
                'CANCELED' => $payment->update(['status' => PaymentStatus::Canceled, 'fawry_response' => $payload]),
                'REFUNDED' => $payment->update(['status' => PaymentStatus::Refunded, 'fawry_response' => $payload]),
                default => Log::info("Fawry webhook: unhandled status [{$orderStatus}]", $payload),
            };
        });

        return response()->json(['message' => 'OK']);
    }

    protected function markAsPaid(Payment $payment, array $payload): void
    {
        $payment->update([
            'status' => PaymentStatus::Paid,
            'payment_method' => $payload['paymentMethod'] ?? $payment->payment_method,
            'fawry_reference_number' => $payload['fawryRefNumber'] ?? $payment->fawry_reference_number,
            'fawry_response' => $payload,
            'paid_at' => now(),
        ]);

        // Unlock the course for the student.
        $payment->enrollment?->activate();
    }

    protected function markAsExpired(Payment $payment, array $payload): void
    {
        $payment->update(['status' => PaymentStatus::Expired, 'fawry_response' => $payload]);
        $payment->enrollment?->update(['status' => EnrollmentStatus::Expired]);
    }
}
