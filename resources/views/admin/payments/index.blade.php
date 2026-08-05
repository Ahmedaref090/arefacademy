@extends('layouts.app')
@section('title', 'Payments – Aref Academy')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">Payments</h1>
    <div class="flex gap-2 text-sm">
        <a class="{{ request('status', 'pending') === 'pending' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => 'pending']) }}">Pending</a>
        <a class="{{ request('status') === 'history' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => 'history']) }}">History</a>
        <a class="{{ request('status') === '' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => '']) }}">All</a>
    </div>
</div>

@if(session('status'))
    <div class="card mb-4 border-emerald-500 text-sm">{{ session('status') }}</div>
@endif

@error('rejection_reason')
    <div class="card mb-4 border-red-500 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
@enderror

<form method="GET" class="card mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="label" for="status">Status</label>
        <select class="input" id="status" name="status">
            <option value="" @selected(request('status', 'pending') === '')>All</option>
            <option value="history" @selected(request('status') === 'history')>History (reviewed)</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status', 'pending') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="course">Course</label>
        <select class="input" id="course" name="course">
            <option value="">All</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected((string) request('course') === (string) $course->id)>{{ $course->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-52 flex-1">
        <label class="label" for="search">Search</label>
        <input class="input w-full" id="search" name="search" value="{{ request('search') }}" placeholder="Student name, phone or sender details">
    </div>
    <button class="btn">Filter</button>
</form>

<div class="card overflow-x-auto p-0">
    <table class="w-full min-w-[960px] text-sm">
        <thead>
            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-400 dark:border-gray-800">
                <th class="px-5 py-3">Student</th>
                <th class="px-5 py-3">Course</th>
                <th class="px-5 py-3">Amount</th>
                <th class="px-5 py-3">Method</th>
                <th class="px-5 py-3">Sender</th>
                <th class="px-5 py-3">Receipt</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3">Reviewed</th>
                <th class="px-5 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($payments as $payment)
                <tr>
                    <td class="px-5 py-3">
                        <div class="font-medium">{{ $payment->user->name }}</div>
                        <div class="font-mono text-xs text-gray-400" dir="ltr">{{ $payment->user->phone }}</div>
                    </td>
                    <td class="px-5 py-3">{{ $payment->course->title }}</td>
                    <td class="px-5 py-3 font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-5 py-3">{{ $payment->payment_method?->label() ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono" dir="ltr">{{ $payment->sender_details ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($payment->receipt_image_path)
                            <a href="{{ route('admin.files.show', $payment->receipt_image_path) }}" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">View</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($payment->isPending())
                            <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Pending</span>
                        @elseif($payment->isApproved())
                            <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Approved</span>
                        @else
                            <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">Rejected</span>
                            @if($payment->rejection_reason)
                                <div class="mt-1 max-w-48 text-xs text-red-500 dark:text-red-400" title="{{ $payment->rejection_reason }}">
                                    {{ Str::limit($payment->rejection_reason, 60) }}
                                </div>
                            @endif
                        @endif
                    </td>
                    <td class="px-5 py-3 text-xs text-gray-400">
                        @if($payment->reviewed_at)
                            {{ $payment->reviewed_at->format('Y-m-d H:i') }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($payment->isPending())
                            <div class="flex flex-col gap-2">
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" onsubmit="return confirm('Approve this payment and activate the 30-day subscription?')">
                                    @csrf
                                    <button class="btn">Approve</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="flex flex-col gap-1">
                                    @csrf
                                    <textarea name="rejection_reason" rows="2" required maxlength="500"
                                        class="input w-44 text-xs"
                                        placeholder="Rejection reason (shown to student)…"></textarea>
                                    <button class="btn-danger">Reject</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-5 py-4 text-center text-gray-400">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $payments->links() }}</div>
@endsection
