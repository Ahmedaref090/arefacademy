<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Models\Course;
use App\Models\CourseMonth;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Show the checkout page (transfer instructions + receipt form).
     *
     * For per-month courses the selected month arrives as a query string
     * (?course_month_id=…) from the course page subscription form.
     */
    public function checkout(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        if ($this->hasActiveEnrollment($user, $course)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('status', 'أنت مشترك بالفعل في هذا الكورس.');
        }

        // Resolve the selected month for per-month courses.
        $courseMonth = $this->resolveCourseMonth($request, $course);

        if ($course->isPerMonth() && ! $courseMonth) {
            // No month selected — send the student back to pick one.
            return redirect()->route('courses.show', $course);
        }

        // A receipt for this month is already awaiting review — show it instead.
        if ($payment = $this->pendingPayment($user, $course, $courseMonth)) {
            return redirect()->route('payments.show', $payment);
        }

        if ($courseMonth && $this->hasPendingOrApprovedMonth($user, $courseMonth)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', __('messages.already_subscribed'));
        }

        return view('payments.checkout', [
            'course' => $course,
            'courseMonth' => $courseMonth,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * Handle the checkout form: free enrollment or manual transfer receipt.
     */
    public function pay(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        if ($this->hasActiveEnrollment($user, $course)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('status', 'أنت مشترك بالفعل في هذا الكورس.');
        }

        // course_month_id is required when the course is priced per month.
        $request->validate([
            'course_month_id' => [
                $course->isPerMonth() ? 'required' : 'nullable',
                'integer',
                'exists:course_months,id',
            ],
        ]);

        // Resolve the month and make sure it belongs to this course.
        $courseMonth = null;
        if ($request->filled('course_month_id')) {
            $courseMonth = $course->months()->findOrFail($request->integer('course_month_id'));
        }

        if ($course->isPerMonth() && ! $courseMonth) {
            return redirect()->route('courses.show', $course);
        }

        // Free course: activate the enrollment immediately.
        if ((float) $course->price <= 0) {
            Enrollment::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                [
                    'status' => EnrollmentStatus::Active,
                    'enrolled_at' => now(),
                    'expires_at' => now()->addDays(30),
                ]
            );

            return redirect()
                ->route('courses.show', $course)
                ->with('status', 'تم تفعيل اشتراكك المجاني.');
        }

        // A payment for this month is already pending review — show it instead.
        if ($payment = $this->pendingPayment($user, $course, $courseMonth)) {
            return redirect()->route('payments.show', $payment);
        }

        // Double-subscription prevention (re-checked at submission time).
        if ($courseMonth && $this->hasPendingOrApprovedMonth($user, $courseMonth)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('error', __('messages.already_subscribed'));
        }

        $data = $request->validate([
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'sender_details' => ['required', 'string', 'max:255'],
            'receipt' => ['required', 'image', 'max:5120'],
        ]);

        // Receipts are PRIVATE — served to admins via admin.files.show.
        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => EnrollmentStatus::Pending]
        );

        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'course_month_id' => $courseMonth?->id,
            'enrollment_id' => $enrollment->id,
            'amount' => $course->price,
            'status' => PaymentStatus::Pending,
            'payment_method' => $data['payment_method'],
            'sender_details' => $data['sender_details'],
            'receipt_image_path' => $receiptPath,
        ]);

        // Record the month subscription as pending: the student can see which
        // month they requested, and the month disappears from the dropdown.
        if ($courseMonth) {
            $user->courseMonths()->syncWithoutDetaching([
                $courseMonth->id => ['status' => PurchaseStatus::Pending],
            ]);
        }

        return redirect()
            ->route('courses.show', $course)
            ->with('status', __('messages.receipt_submitted'));
    }

    /**
     * Show a payment's review status page to its owner.
     */
    public function show(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payment->load('course', 'courseMonth');

        return view('payments.show', compact('payment'));
    }

    /**
     * Resolve the selected course month from the request, ensuring it
     * belongs to the given course. Returns null when none was selected.
     */
    protected function resolveCourseMonth(Request $request, Course $course): ?CourseMonth
    {
        if (! $request->filled('course_month_id')) {
            return null;
        }

        return $course->months()->findOrFail($request->integer('course_month_id'));
    }

    /**
     * True when the student already has a pending or approved request
     * for the given month.
     */
    protected function hasPendingOrApprovedMonth(User $user, CourseMonth $month): bool
    {
        return $user->courseMonths()
            ->wherePivot('course_month_id', $month->id)
            ->wherePivotIn('status', [PurchaseStatus::Pending, PurchaseStatus::Approved])
            ->exists();
    }

    protected function hasActiveEnrollment(User $user, Course $course): bool
    {
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        return $enrollment
            && $enrollment->status === EnrollmentStatus::Active
            && ! $enrollment->isExpired();
    }

    protected function pendingPayment(User $user, Course $course, ?CourseMonth $month = null): ?Payment
    {
        return Payment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', PaymentStatus::Pending)
            ->when($month, fn ($q) => $q->where('course_month_id', $month->id))
            ->latest()
            ->first();
    }
}
