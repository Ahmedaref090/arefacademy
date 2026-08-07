@extends('layouts.app')
@section('title', __('Admin Dashboard – Aref Academy'))

@section('content')
<h1 class="page-title mb-6 text-3xl font-extrabold">{{ __('Dashboard') }}</h1>

<div class="mb-8 grid grid-cols-2 gap-5 lg:grid-cols-4">
    <div class="card"><div class="flex items-center gap-3"><span class="icon-tile-soft !h-11 !w-11"><x-icon name="users" class="h-5 w-5" :stroke="1.8"/></span><div class="text-3xl font-extrabold text-brand-600 dark:text-brand-300">{{ $stats['students'] }}</div></div><div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Students') }}</div></div>
    <div class="card"><div class="flex items-center gap-3"><span class="icon-tile-soft !h-11 !w-11"><x-icon name="book" class="h-5 w-5" :stroke="1.8"/></span><div class="text-3xl font-extrabold text-sky-500 dark:text-sky-400">{{ $stats['courses'] }}</div></div><div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Courses') }}</div></div>
    <div class="card"><div class="flex items-center gap-3"><span class="icon-tile-soft !h-11 !w-11"><x-icon name="layers" class="h-5 w-5" :stroke="1.8"/></span><div class="text-3xl font-extrabold text-amber-500">{{ $stats['active_enrollments'] }}</div></div><div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Active Enrollments') }}</div></div>
    <div class="card"><div class="flex items-center gap-3"><span class="inline-flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-md shadow-emerald-500/30"><x-icon name="wallet" class="h-5 w-5" :stroke="1.8"/></span><div class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ number_format($stats['revenue'], 2) }}</div></div><div class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('Revenue (EGP)') }}</div></div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="card p-0">
        <h2 class="flex items-center gap-2 border-b border-gray-200 p-4 font-bold dark:border-gray-800"><x-icon name="credit" class="h-5 w-5 text-brand-500" :stroke="1.8"/> {{ __('Recent Payments') }}</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">{{ __('Student') }}</th><th class="table-th">{{ __('Course') }}</th><th class="table-th">{{ __('Amount') }}</th><th class="table-th">{{ __('Status') }}</th></tr></thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td">{{ $payment->user->name }}</td>
                        <td class="table-td">{{ $payment->course->title }}</td>
                        <td class="table-td font-mono">{{ number_format($payment->amount, 2) }}</td>
                        <td class="table-td"><span class="badge {{ $payment->isPaid() ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">{{ $payment->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">{{ __('No payments yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-0 !shadow-md">
        <h2 class="flex items-center gap-2 border-b border-gray-200 p-4 font-bold dark:border-gray-800"><x-icon name="users" class="h-5 w-5 text-brand-500" :stroke="1.8"/> {{ __('New Students') }}</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">{{ __('Name') }}</th><th class="table-th">{{ __('Phone') }}</th><th class="table-th">{{ __('Governorate') }}</th><th class="table-th">{{ __('Grade') }}</th></tr></thead>
            <tbody>
                @forelse($recentStudents as $student)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td"><a class="text-indigo-600 dark:text-indigo-400" href="{{ route('admin.students.show', $student) }}">{{ $student->name }}</a></td>
                        <td class="table-td font-mono" dir="ltr">{{ $student->phone }}</td>
                        <td class="table-td">{{ $student->governorate }}</td>
                        <td class="table-td">{{ $student->grade_level?->label() }}</td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">{{ __('No students yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
