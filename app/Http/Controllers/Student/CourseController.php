<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingType;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseMonth;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $courses = Course::where('is_published', true)
            ->withCount('lessons')
            ->with(['enrollments' => fn ($q) => $q->where('user_id', $request->user()->id)])
            ->latest()
            ->paginate(12);

        // Monthly courses for the dependent Course → Month subscription widget.
        $monthlyCourses = Course::where('is_published', true)
            ->where('pricing_type', PricingType::PerMonth->value)
            ->with('months')
            ->orderBy('id')
            ->get()
            ->filter(fn (Course $course) => $course->months->isNotEmpty())
            ->sortBy('title')
            ->values();

        return view('student.courses.index', compact('courses', 'monthlyCourses'));
    }

    /**
     * JSON list of months for a per-month course, consumed by the dependent
     * dropdown on the All Courses page. Months the student already requested
     * (pending) or was granted (approved) are excluded.
     */
    public function months(Request $request, Course $course)
    {
        $excludedIds = $request->user()->courseMonths()
            ->where('course_months.course_id', $course->id)
            ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
            ->pluck('course_months.id')
            ->all();

        $months = $course->months()
            ->whereNotIn('course_months.id', $excludedIds)
            ->orderBy('sort_order')
            ->get(['id', 'name'])
            ->map(fn (CourseMonth $month) => [
                'id' => $month->id,
                'name' => $month->name,
            ])
            ->values();

        return response()->json($months);
    }

    public function my(Request $request)
    {
        $user = $request->user();

        // "Full Courses" section — STRICTLY full-course enrollments only.
        // Month-scoped enrollments (course_month_id != null) are excluded so a
        // monthly course can never appear under both sections.
        $enrollments = $user->enrollments()
            ->with('course.lessons')
            ->with('month')
            ->where('status', EnrollmentStatus::Active)
            ->whereNull('course_month_id')
            ->latest('enrolled_at')
            ->get();

        // "Monthly Subscriptions" section — STRICTLY approved month pivots.
        // These come from approved receipts or admin manual monthly enrolls.
        $approvedMonths = $user->courseMonths()
            ->with('course')
            ->wherePivot('status', PurchaseStatus::Approved)
            ->get()
            ->sortBy([['course.title', 'asc'], ['sort_order', 'asc']])
            ->values();

        return view('student.courses.my', compact('enrollments', 'approvedMonths'));
    }

    public function show(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $course->load(['lessons', 'months.lessons']);

        $user = $request->user();

        // Active AND non-expired — gates content access and the WhatsApp button.
        $hasActiveSubscription = $user->hasActiveSubscriptionTo($course);

        // The raw enrollment record (may be expired) — used to show the
        // "subscription expired, renew" state on the course page.
        $enrollment = $user->activeEnrollmentIn($course);

        $pendingPayment = $user->payments()
            ->where('course_id', $course->id)
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        // Months the student has already requested (pending) or been granted
        // (approved) — these are excluded from the subscription dropdown.
        $monthSubscriptions = $user->courseMonths()
            ->where('course_months.course_id', $course->id)
            ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
            ->get();

        // Approved months unlock that month's lessons in the content list.
        $approvedMonthIds = $monthSubscriptions
            ->where('pivot.status', PurchaseStatus::Approved->value)
            ->pluck('id')
            ->all();

        // Months still available for a new subscription request.
        $availableMonths = $course->months
            ->whereNotIn('id', $monthSubscriptions->pluck('id'))
            ->values();

        return view('student.courses.show', compact(
            'course',
            'hasActiveSubscription',
            'enrollment',
            'pendingPayment',
            'approvedMonthIds',
            'availableMonths'
        ));
    }
}
