<?php

namespace Tests\Feature;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FawryWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Payment $payment;
    protected Enrollment $enrollment;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('fawry.security_key', 'test-key');

        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 250]);

        $this->enrollment = Enrollment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => EnrollmentStatus::Pending,
        ]);

        $this->payment = Payment::create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_id' => $this->enrollment->id,
            'merchant_ref_number' => 'AREF-TEST-0001',
            'amount' => 250,
            'status' => PaymentStatus::Pending,
        ]);
    }

    protected function signedPayload(array $overrides = []): array
    {
        $payload = array_merge([
            'fawryRefNumber' => '9701234567',
            'merchantRefNum' => $this->payment->merchant_ref_number,
            'paymentAmount' => '250.00',
            'orderAmount' => '250.00',
            'orderStatus' => 'PAID',
            'paymentMethod' => 'PAYATFAWRY',
            'paymentRefrenceNumber' => '',
        ], $overrides);

        $payload['messageSignature'] = hash('sha256',
            $payload['fawryRefNumber']
            . $payload['merchantRefNum']
            . $payload['paymentAmount']
            . $payload['orderAmount']
            . $payload['orderStatus']
            . $payload['paymentMethod']
            . $payload['paymentRefrenceNumber']
            . 'test-key'
        );

        return $payload;
    }

    public function test_paid_webhook_marks_payment_and_activates_enrollment(): void
    {
        $this->postJson(route('webhooks.fawry'), $this->signedPayload())->assertOk();

        $this->assertEquals(PaymentStatus::Paid, $this->payment->fresh()->status);
        $this->assertEquals(EnrollmentStatus::Active, $this->enrollment->fresh()->status);
        $this->assertNotNull($this->payment->fresh()->paid_at);
    }

    public function test_expired_webhook_expires_payment_and_enrollment(): void
    {
        $this->postJson(route('webhooks.fawry'), $this->signedPayload(['orderStatus' => 'EXPIRED']))->assertOk();

        $this->assertEquals(PaymentStatus::Expired, $this->payment->fresh()->status);
        $this->assertEquals(EnrollmentStatus::Expired, $this->enrollment->fresh()->status);
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = $this->signedPayload();
        $payload['messageSignature'] = 'invalid-signature';

        $this->postJson(route('webhooks.fawry'), $payload)->assertForbidden();

        $this->assertEquals(PaymentStatus::Pending, $this->payment->fresh()->status);
    }

    public function test_webhook_returns_404_for_unknown_reference(): void
    {
        $this->postJson(route('webhooks.fawry'), $this->signedPayload([
            'merchantRefNum' => 'AREF-UNKNOWN-REF',
        ]))->assertNotFound();
    }
}
