<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Services\FawryPaymentService;
use Illuminate\Http\Request;
use Throwable;

class PaymentController extends Controller
{
    public function checkout(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);
        abort_if($request->user()->isEnrolledIn($course), 403);

        return view('payments.checkout', compact('course'));
    }

    public function pay(Request $request, Course $course, FawryPaymentService $fawry)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();
        abort_if($user->isEnrolledIn($course), 403);

        // Free course: enroll immediately, no Fawry charge needed.
        if ((float) $course->price <= 0) {
            Enrollment::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                ['status' => EnrollmentStatus::Active, 'enrolled_at' => now()]
            );

            return redirect()->route('courses.show', $course)->with('status', 'Enrolled successfully.');
        }

        try {
            $payment = $fawry->createChargeRequest($user, $course);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'payment' => 'Could not reach Fawry right now. Please try again in a moment.',
            ]);
        }

        return redirect()->route('payments.show', $payment);
    }

    public function show(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payment->load('course');

        return view('payments.show', compact('payment'));
    }
}
