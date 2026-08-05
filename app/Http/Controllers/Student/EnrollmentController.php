<?php

namespace App\Http\Controllers\Student;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Handle a student's subscription request.
     *
     * - Lifetime courses are purchased through the normal payment checkout.
     * - Per-month courses attach the chosen month to the course_month_user
     *   pivot with a 'pending' status until an admin approves it.
     */
    public function store(Request $request)
    {
        $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
        ]);

        $course = Course::findOrFail($request->input('course_id'));

        // Full-course (lifetime) purchases keep using the existing checkout flow.
        if (! $course->isPerMonth()) {
            return redirect()->route('payments.checkout', $course);
        }

        // course_month_id is required for per-month courses.
        $data = $request->validate([
            'course_month_id' => ['required', 'exists:course_months,id'],
        ]);

        // 404 if the selected month does not belong to this course.
        $month = $course->months()->findOrFail($data['course_month_id']);

        // Double-subscription prevention: block when a pending or approved
        // request already exists for this month.
        $alreadySubscribed = $request->user()->courseMonths()
            ->wherePivot('course_month_id', $month->id)
            ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
            ->exists();

        if ($alreadySubscribed) {
            return back()->with('error', __('messages.already_subscribed'));
        }

        // syncWithoutDetaching attaches with 'pending' — and safely re-uses an
        // old (e.g. rejected) row instead of creating a duplicate.
        $request->user()->courseMonths()->syncWithoutDetaching([
            $month->id => ['status' => PurchaseStatus::Pending],
        ]);

        return back()->with('status', __('messages.subscription_requested'));
    }
}
