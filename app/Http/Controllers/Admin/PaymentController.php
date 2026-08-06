<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * List manual payments. Defaults to the pending review queue.
     * The status filter can show a single status, all payments, or the
     * "history" log (approved + rejected transactions).
     */
    public function index(Request $request)
    {
        // First visit (no query string) defaults to the pending queue;
        // choosing "All" submits status='' which means no status filter.
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : PaymentStatus::Pending->value;

        $payments = Payment::with('user', 'course', 'courseMonth')
            ->when($status === 'history', fn ($q) => $q->reviewed())
            ->when($status !== '' && $status !== 'history', fn ($q) => $q->where('status', $status))
            ->when($request->filled('course'), fn ($q) => $q
                ->where('course_id', $request->integer('course')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->toString() . '%';
                $q->where(function ($s) use ($term) {
                    $s->where('sender_details', 'like', $term)
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'courses' => Course::orderBy('title')->get(),
            'statuses' => PaymentStatus::cases(),
        ]);
    }

    /**
     * Approve a pending manual payment. The payment record is KEPT (never
     * deleted) — it becomes part of the payment history log.
     *
     * - Month payment (per-month course): the course_month_user pivot flips
     *   to "approved", which unlocks that month's lessons and makes the
     *   course appear in the student's "My Courses".
     * - Full-course payment (lifetime): the enrollment is activated for
     *   30 days as before.
     */
    public function approve(Payment $payment)
    {
        abort_unless($payment->isPending(), 422);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => PaymentStatus::Approved,
                'paid_at' => now(),
                'expires_at' => now()->addDays(30),
                'reviewed_at' => now(),
            ]);

            if ($payment->course_month_id) {
                $payment->user->courseMonths()->syncWithoutDetaching([
                    $payment->course_month_id => ['status' => PurchaseStatus::Approved],
                ]);
            } else {
                // Sets the enrollment to active with expires_at = now + 30 days.
                $payment->enrollment?->activate();
            }
        });

        return back()->with('status', 'Payment approved — 30-day subscription activated.');
    }

    /**
     * Reject a pending manual payment with a reason shown to the student.
     * The payment record is KEPT (never deleted) — it becomes part of
     * the payment history log.
     */
    public function reject(Request $request, Payment $payment)
    {
        abort_unless($payment->isPending(), 422);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'rejection_reason' => $data['rejection_reason'],
            'reviewed_at' => now(),
        ]);

        // Month payment rejected: flip the pivot to "rejected" so the month
        // becomes available again in the course page dropdown.
        if ($payment->course_month_id) {
            $payment->user->courseMonths()->syncWithoutDetaching([
                $payment->course_month_id => ['status' => PurchaseStatus::Rejected],
            ]);
        }

        return back()->with('status', 'Payment rejected.');
    }
}
