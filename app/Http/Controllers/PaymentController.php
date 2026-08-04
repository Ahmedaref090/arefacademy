<?php

namespace App\Http\Controllers;

use App\Enums\EnrollmentStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Show the checkout page (transfer instructions + receipt form).
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

        // A receipt is already awaiting admin review — show it instead.
        if ($payment = $this->pendingPayment($user, $course)) {
            return redirect()->route('payments.show', $payment);
        }

        return view('payments.checkout', [
            'course' => $course,
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

        // A payment is already pending review — don't accept another receipt.
        if ($payment = $this->pendingPayment($user, $course)) {
            return redirect()->route('payments.show', $payment);
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
            'enrollment_id' => $enrollment->id,
            'amount' => $course->price,
            'status' => PaymentStatus::Pending,
            'payment_method' => $data['payment_method'],
            'sender_details' => $data['sender_details'],
            'receipt_image_path' => $receiptPath,
        ]);

        return redirect()
            ->route('payments.show', $payment)
            ->with('status', 'تم استلام إيصالك، وهو الآن قيد المراجعة.');
    }

    /**
     * Show a payment's review status page to its owner.
     */
    public function show(Request $request, Payment $payment)
    {
        abort_unless($payment->user_id === $request->user()->id, 403);

        $payment->load('course');

        return view('payments.show', compact('payment'));
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

    protected function pendingPayment(User $user, Course $course): ?Payment
    {
        return Payment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();
    }
}
