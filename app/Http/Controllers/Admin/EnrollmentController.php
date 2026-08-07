<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EnrollmentStatus;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMonth;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * JSON list of months for a course, used by the dependent dropdown
     * when enrolling a student into a monthly course.
     */
    public function months(Course $course)
    {
        return response()->json(
            $course->months()
                ->orderBy('sort_order')
                ->get(['id', 'name'])
                ->map(fn (CourseMonth $month) => [
                    'id' => $month->id,
                    'name' => $month->name,
                ])
        );
    }

    /**
     * Manually enroll a student in a course (cash sales, scholarships...).
     *
     * This exactly mirrors the state a student reaches after their payment
     * receipt is approved, so the course appears in ONLY the correct section
     * of the student dashboard:
     *
     * - Per-month course → only the course_month_user pivot is marked "approved".
     *   No active full-course Enrollment is created, so it shows under
     *   "Monthly Subscriptions" (never under "Full Courses").
     * - Lifetime / full course → an active Enrollment (course_month_id = null)
     *   showing under "Full Courses".
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'course_id' => ['required', 'exists:courses,id'],
            'course_month_id' => ['nullable', 'integer', 'exists:course_months,id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $course = Course::findOrFail($data['course_id']);
        $courseMonthId = $data['course_month_id'] ?? null;

        // ── Monthly course: month is required ──────────────────────────────
        if ($course->isPerMonth()) {
            if ($courseMonthId === null) {
                return back()
                    ->withErrors(['course_month_id' => __('Please select a month for this monthly course.')])
                    ->withInput();
            }

            $belongsToCourse = CourseMonth::where('course_id', $course->id)
                ->whereKey($courseMonthId)
                ->exists();

            if (! $belongsToCourse) {
                return back()
                    ->withErrors(['course_month_id' => __('The selected month does not belong to this course.')])
                    ->withInput();
            }

            // Mirror an approved monthly receipt: ONLY the approved pivot.
            // No active full-course Enrollment is created here.
            $user->courseMonths()->syncWithoutDetaching([
                $courseMonthId => ['status' => PurchaseStatus::Approved],
            ]);

            $month = CourseMonth::findOrFail($courseMonthId);

            return back()->with('status', __('Subscribed: :course – :month (30-day access).', [
                'course' => $course->title,
                'month' => $month->name,
            ]));
        }

        // ── Lifetime / full course: active Enrollment, no month ────────────
        Enrollment::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'course_month_id' => null,
            ],
            [
                'status' => EnrollmentStatus::Active,
                'enrolled_at' => now(),
                'expires_at' => now()->addDays(30),
            ]
        );

        return back()->with('status', __('Student enrolled successfully (30-day subscription).'));
    }

    public function destroy(Enrollment $enrollment)
    {
        $enrollment->delete();

        return back()->with('status', __('Enrollment removed.'));
    }

    /**
     * Revoke a specific month subscription (the course_month_user pivot) for
     * a student. Monthly access from the checkout flow lives on this pivot,
     * not on an Enrollment row, so it needs its own cancelling action.
     */
    public function destroyMonth(User $user, CourseMonth $courseMonth)
    {
        abort_unless($user->isStudent(), 404);

        $user->courseMonths()->detach($courseMonth);

        return back()->with('status', __('Subscription revoked: :course – :month.', [
            'course' => $courseMonth->course->title,
            'month' => $courseMonth->name,
        ]));
    }
}
