<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_paid_course_creates_pending_payment_and_enrollment(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['price' => 250, 'is_published' => true]);

        $this->actingAs($student)->post(route('payments.pay', $course), [
            'payment_method' => 'vodafone_cash',
            'sender_details' => '01234567890',
            'receipt' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $payment = Payment::where('user_id', $student->id)->first();

        $this->assertNotNull($payment);
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
        $this->actingAs($student)->post(route('payments.pay', $course))
            ->assertRedirect(route('courses.show', $course));
    }

    public function test_unpublished_course_cannot_be_purchased(): void
    {
        $student = User::factory()->create();
        $course = Course::factory()->create(['is_published' => false]);

        $this->actingAs($student)->post(route('payments.pay', $course))->assertNotFound();
    }
}
