@extends('layouts.app')
@section('title', 'Admin Dashboard – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Dashboard</h1>

<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="card"><div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['students'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Students</div></div>
    <div class="card"><div class="text-3xl font-bold text-sky-500">{{ $stats['courses'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Courses</div></div>
    <div class="card"><div class="text-3xl font-bold text-amber-500">{{ $stats['active_enrollments'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Active Enrollments</div></div>
    <div class="card"><div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['revenue'], 2) }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Revenue (EGP)</div></div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="card p-0">
        <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">Recent Payments</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Student</th><th class="table-th">Course</th><th class="table-th">Amount</th><th class="table-th">Status</th></tr></thead>
            <tbody>
                @forelse($recentPayments as $payment)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td">{{ $payment->user->name }}</td>
                        <td class="table-td">{{ $payment->course->title }}</td>
                        <td class="table-td font-mono">{{ number_format($payment->amount, 2) }}</td>
                        <td class="table-td"><span class="badge {{ $payment->isPaid() ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">{{ $payment->status->label() }}</span></td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">No payments yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-0">
        <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">New Students</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Name</th><th class="table-th">Phone</th><th class="table-th">Governorate</th><th class="table-th">Grade</th></tr></thead>
            <tbody>
                @forelse($recentStudents as $student)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td"><a class="text-indigo-600 dark:text-indigo-400" href="{{ route('admin.students.show', $student) }}">{{ $student->name }}</a></td>
                        <td class="table-td font-mono" dir="ltr">{{ $student->phone }}</td>
                        <td class="table-td">{{ $student->governorate }}</td>
                        <td class="table-td">{{ $student->grade_level?->label() }}</td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">No students yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
