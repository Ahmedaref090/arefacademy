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
    public function index(Request $request)
    {
        $payments = Payment::with('user', 'course')
            ->when($request->filled('status'), fn ($q) => $q
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('course'), fn ($q) => $q
                ->where('course_id', $request->integer('course')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->string('search')->toString() . '%';
                $q->where(function ($s) use ($term) {
                    $s->where('merchant_ref_number', 'like', $term)
                        ->orWhere('fawry_reference_number', 'like', $term)
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
     * Manually mark a payment as paid (e.g. student paid in cash)
     * and activate the linked enrollment.
     */
    public function markPaid(Payment $payment)
    {
        abort_if($payment->isPaid(), 422);

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => PaymentStatus::Paid,
                'payment_method' => $payment->payment_method ?? 'CASH',
                'paid_at' => now(),
            ]);

            $payment->enrollment?->activate();
        });

        return back()->with('status', 'Payment marked as paid — enrollment activated.');
    }
}
