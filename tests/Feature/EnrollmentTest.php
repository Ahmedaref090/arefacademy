<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class EnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_enroll_in_free_course_without_payment(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 0, 'is_published' => true]);

        $this->actingAs($student)
            ->post(route('payments.pay', $course))
            ->assertRedirect(route('courses.show', $course));

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);
    }

    public function test_paid_course_creates_fawry_payment_and_pending_enrollment(): void
    {
        config()->set('fawry.merchant_code', 'test-merchant');
        config()->set('fawry.security_key', 'test-key');

        Http::fake([
            '*' => Http::response(['referenceNumber' => '987654321'], 200),
        ]);

        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 250, 'is_published' => true]);

        $this->actingAs($student)->post(route('payments.pay', $course));

        $payment = Payment::where('user_id', $student->id)->first();

        $this->assertNotNull($payment);
        $this->assertEquals('987654321', $payment->fawry_reference_number);
        $this->assertEquals('250.00', $payment->amount);
        $this->assertDatabaseHas('enrollments', [
            'user_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);
    }

    public function test_enrolled_student_cannot_pay_again(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 0, 'is_published' => true]);

        $this->actingAs($student)->post(route('payments.pay', $course));
        $this->actingAs($student)->post(route('payments.pay', $course))->assertForbidden();
    }

    public function test_unpublished_course_cannot_be_purchased(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['is_published' => false]);

        $this->actingAs($student)->post(route('payments.pay', $course))->assertNotFound();
    }
}
