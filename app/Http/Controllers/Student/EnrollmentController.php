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
        $courseId = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ])['course_id'];

        $course = Course::findOrFail($courseId);

        // Full-course (lifetime) purchases go straight to checkout, no month needed.
        if (! $course->isPerMonth()) {
            return redirect()->route('payments.checkout', $course);
        }

        // One or more months are required for per-month courses.
        $data = $request->validate([
            'course_month_ids' => ['required', 'array', 'min:1'],
            'course_month_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        // 404-equivalent guard: the selected months MUST belong to this course.
        $monthIds = collect(array_unique(array_map('intval', $data['course_month_ids'])))->values();
        $months = $course->months()->whereIn('id', $monthIds)->get();

        if ($months->count() !== $monthIds->count()) {
            return back()
                ->withErrors(['course_month_ids' => __('One or more selected months are invalid.')])
                ->withInput();
        }

        // Double-subscription prevention for every selected month.
        foreach ($months as $month) {
            $alreadySubscribed = $request->user()->courseMonths()
                ->wherePivot('course_month_id', $month->id)
                ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
                ->exists();

            if ($alreadySubscribed) {
                return back()->with('error', __('messages.already_subscribed'));
            }
        }

        // Hand off to checkout carrying every selected month:
        // /courses/{course}/checkout?course_month_ids[]=…&course_month_ids[]=…
        return redirect()->route('payments.checkout', [
            'course' => $course,
            'course_month_ids' => $monthIds->map(fn ($id) => (string) $id)->all(),
        ]);
    }
}
