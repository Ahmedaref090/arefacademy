@extends('layouts.app')
@section('title', __('Payments – Aref Academy'))

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <h1 class="text-2xl font-bold">{{ __('Payments') }}</h1>
    <div class="flex gap-2 text-sm">
        <a class="{{ request('status', 'pending') === 'pending' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => 'pending']) }}">{{ __('Pending') }}</a>
        <a class="{{ request('status') === 'history' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => 'history']) }}">{{ __('History') }}</a>
        <a class="{{ request('status') === '' ? 'btn' : 'btn-secondary' }}" href="{{ route('admin.payments.index', ['status' => '']) }}">{{ __('All') }}</a>
    </div>
</div>

@error('rejection_reason')
    <div class="card mb-4 border-red-500 text-sm text-red-600 dark:text-red-400">{{ $message }}</div>
@enderror

<form method="GET" class="card mb-6 flex flex-wrap items-end gap-3">
    <div>
        <label class="label" for="status">{{ __('Status') }}</label>
        <select class="input" id="status" name="status">
            <option value="" @selected(request('status', 'pending') === '')>{{ __('All') }}</option>
            <option value="history" @selected(request('status') === 'history')>{{ __('History (reviewed)') }}</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status', 'pending') === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="label" for="course">{{ __('Course') }}</label>
        <select class="input" id="course" name="course">
            <option value="">{{ __('All') }}</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected((string) request('course') === (string) $course->id)>{{ $course->title }}</option>
            @endforeach
        </select>
    </div>
    <div class="min-w-52 flex-1">
        <label class="label" for="search">{{ __('Search') }}</label>
        <input class="input w-full" id="search" name="search" value="{{ request('search') }}" placeholder="{{ __('Student name, phone or sender details') }}">
    </div>
    <button class="btn">{{ __('Filter') }}</button>
</form>

<div class="card overflow-x-auto p-0">
    <table class="w-full min-w-[1040px] text-sm">
        <thead>
            <tr class="border-b border-gray-200 text-left text-xs uppercase text-gray-400 dark:border-gray-800">
                <th class="px-5 py-3">{{ __('Student') }}</th>
                <th class="px-5 py-3">{{ __('Course') }}</th>
                <th class="px-5 py-3">{{ __('Month') }}</th>
                <th class="px-5 py-3">{{ __('Amount') }}</th>
                <th class="px-5 py-3">{{ __('Method') }}</th>
                <th class="px-5 py-3">{{ __('Sender') }}</th>
                <th class="px-5 py-3">{{ __('Receipt') }}</th>
                <th class="px-5 py-3">{{ __('Status') }}</th>
                <th class="px-5 py-3">{{ __('Reviewed') }}</th>
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
                    <td class="px-5 py-3">
                        @php($displayMonths = $payment->displayMonths())
                        @if($displayMonths->isNotEmpty())
                            <div class="flex flex-wrap gap-1">
                                @foreach($displayMonths as $month)
                                    <span class="badge bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $month->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400">{{ __('Full course') }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-5 py-3">{{ $payment->payment_method?->label() ?? '—' }}</td>
                    <td class="px-5 py-3 font-mono" dir="ltr">{{ $payment->sender_details ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if($payment->receipt_image_path)
                            <a href="{{ route('admin.files.show', $payment->receipt_image_path) }}" target="_blank" class="text-indigo-600 hover:underline dark:text-indigo-400">{{ __('View') }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        @if($payment->isPending())
                            <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">{{ __('Pending') }}</span>
                        @elseif($payment->isApproved())
                            <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ __('Approved') }}</span>
                        @else
                            <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">{{ __('Rejected') }}</span>
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
                                <form method="POST" action="{{ route('admin.payments.approve', $payment) }}" onsubmit="return confirm(@json($payment->displayMonths()->count() > 1 ? __('Approve this payment and activate all the selected months subscriptions?') : ($payment->displayMonths()->isNotEmpty() ? __('Approve this payment and activate the month subscription?') : __('Approve this payment and activate the 30-day subscription?'))))">
                                    @csrf
                                    <button class="btn">{{ __('Approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.payments.reject', $payment) }}" class="flex flex-col gap-1">
                                    @csrf
                                    <textarea name="rejection_reason" rows="2" required maxlength="500"
                                        class="input w-44 text-xs"
                                        placeholder="{{ __('Rejection reason (shown to student)…') }}"></textarea>
                                    <button class="btn-danger">{{ __('Reject') }}</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-5 py-4 text-center text-gray-400">{{ __('No payments found.') }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $payments->links() }}</div>
@endsection
