<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * List manual payments. Defaults to the pending review queue;
     * the status filter can show approved/rejected or all payments.
     */
    public function index(Request $request)
    {
        // First visit (no query string) defaults to the pending queue;
        // choosing "All" submits status='' which means no status filter.
        $status = $request->has('status')
            ? $request->string('status')->toString()
            : PaymentStatus::Pending->value;

        $payments = Payment::with('user', 'course')
            ->when($status !== '', fn ($q) => $q->where('status', $status))
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
     * Approve a pending manual payment and activate the student's
     * 30-day course subscription.
     */
    public function approve(Payment $payment)
    {
        abort_unless($payment->isPending(), 422);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => PaymentStatus::Approved,
                'paid_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            // Sets the enrollment to active with expires_at = now + 30 days.
            $payment->enrollment?->activate();
        });

        return back()->with('status', 'Payment approved — 30-day subscription activated.');
    }

    /**
     * Reject a pending manual payment with a reason shown to the student,
     * so they know what went wrong and can submit a corrected receipt.
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
        ]);

        return back()->with('status', 'Payment rejected.');
    }
}
