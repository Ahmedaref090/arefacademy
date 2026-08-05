<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
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

        return view('student.courses.index', compact('courses'));
    }

    public function my(Request $request)
    {
        $enrollments = $request->user()->enrollments()
            ->with('course.lessons')
            ->where('status', EnrollmentStatus::Active)
            ->latest('enrolled_at')
            ->get();

        return view('student.courses.my', compact('enrollments'));
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
