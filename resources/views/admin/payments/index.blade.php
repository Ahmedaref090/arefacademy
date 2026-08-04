@extends('layouts.app')
@section('title', 'Payments – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Payments</h1>

<form method="GET" action="{{ route('admin.payments.index') }}" class="card mb-6 grid gap-3 md:grid-cols-4">
    <input class="input" name="search" value="{{ request('search') }}" placeholder="Search ref / student / phone…">
    <select class="input" name="course">
        <option value="">All courses</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}" @selected((string) $course->id === request('course'))>{{ $course->title }}</option>
        @endforeach
    </select>
    <select class="input" name="status">
        <option value="">All statuses</option>
        @foreach($statuses as $status)
            <option value="{{ $status->value }}" @selected($status->value === request('status'))>{{ $status->label() }}</option>
        @endforeach
    </select>
    <button class="btn">Filter</button>
</form>

<div class="card p-0">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="table-th">Student</th>
                <th class="table-th">Course</th>
                <th class="table-th">Amount</th>
                <th class="table-th">Fawry Ref</th>
                <th class="table-th">Status</th>
                <th class="table-th">Date</th>
                <th class="table-th"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr class="border-b border-gray-100 dark:border-gray-800/50">
                    <td class="table-td">
                        <div class="font-medium">{{ $payment->user->name }}</div>
                        <div class="font-mono text-xs text-gray-400" dir="ltr">{{ $payment->user->phone }}</div>
                    </td>
                    <td class="table-td">{{ $payment->course->title }}</td>
                    <td class="table-td font-mono">{{ number_format($payment->amount, 2) }}</td>
                    <td class="table-td font-mono text-xs">{{ $payment->fawry_reference_number ?? '—' }}</td>
                    <td class="table-td">
                        <span class="badge {{ $payment->isPaid() ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">{{ $payment->status->label() }}</span>
                    </td>
                    <td class="table-td text-gray-400">{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td class="table-td">
                        @if(! $payment->isPaid())
                            <form method="POST" action="{{ route('admin.payments.mark-paid', $payment) }}" onsubmit="return confirm('Mark this payment as PAID and activate the enrollment?')">
                                @csrf
                                <button class="btn-secondary">Mark Paid</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td class="table-td text-gray-400" colspan="7">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">{{ $payments->links() }}</div>
@endsection
