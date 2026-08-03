@extends('layouts.app')
@section('title', 'Payment – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Payment Details</h1>

<div class="card max-w-xl space-y-4">
    <div class="flex items-center justify-between">
        <span class="font-semibold">{{ $payment->course->title }}</span>
        <span class="badge {{ $payment->isPaid() ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">
            {{ $payment->status->label() }}
        </span>
    </div>

    <div class="text-sm text-gray-500 dark:text-gray-400">
        Amount: <span class="font-mono font-bold text-gray-900 dark:text-gray-100">{{ number_format($payment->amount, 2) }} EGP</span><br>
        Reference: <span class="font-mono">{{ $payment->merchant_ref_number }}</span><br>
        @if($payment->expires_at) Expires: {{ $payment->expires_at->format('Y-m-d H:i') }} @endif
    </div>

    @if($payment->isPaid())
        <div class="rounded-lg bg-green-50 p-4 text-sm text-green-700 dark:bg-green-500/10 dark:text-green-400">
            ✅ Payment confirmed — your course is unlocked!
        </div>
        <a class="btn" href="{{ route('courses.show', $payment->course) }}">Go to Course</a>
    @elseif($payment->fawry_reference_number)
        <div class="rounded-lg bg-gray-100 p-4 text-center dark:bg-gray-800">
            <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Pay at any Fawry outlet with this number</div>
            <div class="mt-1 font-mono text-3xl font-bold tracking-widest text-indigo-600 dark:text-indigo-400">{{ $payment->fawry_reference_number }}</div>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400">Once you pay, this page updates automatically after Fawry notifies us (usually within minutes).</p>
    @else
        <div class="rounded-lg bg-amber-50 p-4 text-sm text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
            Waiting for Fawry to issue a reference number. Please refresh in a moment.
        </div>
    @endif
</div>
@endsection
