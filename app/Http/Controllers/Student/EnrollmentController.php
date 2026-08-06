<?php

namespace App\Http\Controllers\Student;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Handle the "Subscribe to Month" submission from the course page.
     *
     * Nothing is written to the database here: the student is redirected to
     * the checkout page carrying the selected course_month_id, where they
     * upload a payment receipt. The month subscription is only recorded
     * (as pending) once the receipt is submitted — see PaymentController@pay.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $course = Course::findOrFail($request->input('course_id'));

        // Full-course (lifetime) purchases go straight to checkout, no month needed.
        if (! $course->isPerMonth()) {
            return redirect()->route('payments.checkout', $course);
        }

        // course_month_id is required for per-month courses.
        $data = $request->validate([
            'course_month_id' => ['required', 'exists:course_months,id'],
        ]);

        // 404 if the selected month does not belong to this course.
        $month = $course->months()->findOrFail($data['course_month_id']);

        // Double-subscription prevention: don't let the student reach checkout
        // for a month that is already pending or approved.
        $alreadySubscribed = $request->user()->courseMonths()
            ->wherePivot('course_month_id', $month->id)
            ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
            ->exists();

        if ($alreadySubscribed) {
            return back()->with('error', __('messages.already_subscribed'));
        }

        // Hand off to checkout, carrying the selected month in the query string:
        // /courses/{course}/checkout?course_month_id={id}
        return redirect()->route('payments.checkout', [
            'course' => $course,
            'course_month_id' => $month->id,
        ]);
    }
}
