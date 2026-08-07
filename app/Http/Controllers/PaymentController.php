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
use App\Rules\Phone;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentController extends Controller
{
    /**
     * Show the checkout page (transfer instructions + receipt form).
     *
     * For per-month courses the selected months arrive as a query string
     * (?course_month_ids[]=…) from the course page subscription form.
     */
    public function checkout(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        if ($this->hasActiveEnrollment($user, $course)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('status', __('You are already subscribed to this course.'));
        }

        // Resolve every selected month for per-month courses.
        $courseMonths = $course->isPerMonth()
            ? $this->resolveCourseMonths($request, $course)
            : collect();

        if ($course->isPerMonth() && $courseMonths->isEmpty()) {
            // No (or invalid) month selected — send the student back to pick one.
            return redirect()->route('courses.show', $course);
        }

        // Block if any selected month is already pending or granted.
        foreach ($courseMonths as $month) {
            if ($this->hasPendingOrApprovedMonth($user, $month)) {
                return redirect()
                    ->route('courses.show', $course)
                    ->with('error', __('messages.already_subscribed'));
            }
        }

        return view('payments.checkout', [
            'course' => $course,
            'courseMonths' => $courseMonths,
            'methods' => PaymentMethod::cases(),
        ]);
    }

    /**
     * Handle the checkout form: free enrollment or manual transfer receipt.
     *
     * For per-month courses the amount is always derived server-side from the
     * selected months: Required = Course Base Price × number of selected months.
     * The submitted values are only used to decide WHICH months, never the total.
     */
    public function pay(Request $request, Course $course)
    {
        abort_unless($course->is_published, 404);

        $user = $request->user();

        if ($this->hasActiveEnrollment($user, $course)) {
            return redirect()
                ->route('courses.show', $course)
                ->with('status', __('You are already subscribed to this course.'));
        }

        // Re-resolve the selected months from the DB so the total is tamper-proof.
        $courseMonths = $course->isPerMonth()
            ? $this->resolveCourseMonths($request, $course)
            : collect();

        // Free course: activate the (full) enrollment immediately.
        if ((float) $course->price <= 0) {
            Enrollment::updateOrCreate(
                ['user_id' => $user->id, 'course_id' => $course->id],
                [
                    'status' => EnrollmentStatus::Active,
                    'enrolled_at' => now(),
                    'expires_at' => now()->addDays(30 * max(1, $courseMonths->count())),
                ]
            );

            return redirect()
                ->route('courses.show', $course)
                ->with('status', __('Your free subscription has been activated.'));
        }

        // Validity: a per-month course needs >= 1 month, all belonging to it.
        if ($course->isPerMonth()) {
            $postedIds = collect($request->input('course_month_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($courseMonths->isEmpty() || $courseMonths->count() !== $postedIds->count()) {
                return back()
                    ->withErrors(['course_month_ids' => __('One or more selected months are invalid.')])
                    ->withInput();
            }
        }

        // Double-subscription prevention (re-checked at submission time).
        foreach ($courseMonths as $month) {
            if ($this->hasPendingOrApprovedMonth($user, $month)) {
                return redirect()
                    ->route('courses.show', $course)
                    ->with('error', __('messages.already_subscribed'));
            }
        }

        $data = $request->validate([
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'sender_details' => ['required', new Phone],
            'receipt' => ['required', 'image', 'max:5120'],
        ]);

        // Receipts are PRIVATE — served to admins via admin.files.show.
        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        $enrollment = Enrollment::firstOrCreate(
            ['user_id' => $user->id, 'course_id' => $course->id],
            ['status' => EnrollmentStatus::Pending]
        );

        // Server-derived total (used for the admin's review context / logs):
        // Total = Course Base Price × number of selected months.
        $monthCount = max(1, $courseMonths->count());
        $total = (float) $course->price * $monthCount;

        // ONE receipt per checkout, attaching every selected month to it,
        // so the admin reviews a single consolidated payment (One receipt →
        // Many months).
        $payment = Payment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'course_month_id' => null,
            'enrollment_id' => $enrollment->id,
            'amount' => $total,
            'status' => PaymentStatus::Pending,
            'payment_method' => $data['payment_method'],
            'sender_details' => $data['sender_details'],
            'receipt_image_path' => $receiptPath,
        ]);

        if ($courseMonths->isNotEmpty()) {
            // Link every selected month to this single receipt.
            $payment->courseMonths()->attach($courseMonths->pluck('id'));

            // Record each month subscription as pending: the student can see
            // which months they requested, and they leave the dropdown.
            foreach ($courseMonths as $month) {
                $user->courseMonths()->syncWithoutDetaching([
                    $month->id => ['status' => PurchaseStatus::Pending],
                ]);
            }
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

        $payment->load('course', 'courseMonth', 'courseMonths');

        return view('payments.show', compact('payment'));
    }

    /**
     * Stream the payment's receipt image to its owner (or an admin).
     *
     * Receipts are stored on the PRIVATE disk for security, so they are
     * never served from /storage directly. Authorization: only the payment
     * owner (or an admin) may view it, and the path must stay inside the
     * receipts directory — the route can't be used to read arbitrary files.
     */
    public function receipt(Request $request, Payment $payment): StreamedResponse
    {
        $user = $request->user();

        abort_unless($user->isAdmin() || $payment->user_id === $user->id, 403);
        abort_unless($payment->receipt_image_path, 404);

        $path = ltrim($payment->receipt_image_path, '/');

        abort_unless(Str::startsWith($path, 'receipts/'), 403);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }

    /**
     * Resolve the selected course months from the request, ensuring each one
     * belongs to the given course. Returns an (empty) Collection when none was
     * selected. Used for the per-month checkout and payment flows.
     */
    protected function resolveCourseMonths(Request $request, Course $course): Collection
    {
        $ids = collect($request->input('course_month_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return collect();
        }

        return $course->months()
            ->whereIn('id', $ids)
            ->orderBy('sort_order')
            ->get();
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
