@extends('layouts.app')
@section('title', 'Home – Aref Academy')

@section('content')
@php($u = auth()->user())

{{-- ── Profile header ─────────────────────────────────────────── --}}
<div class="card mb-6 flex flex-wrap items-center gap-5">
    @if($u->avatarUrl())
        <img src="{{ $u->avatarUrl() }}" alt="" class="h-20 w-20 rounded-full object-cover ring-4 ring-indigo-500/20">
    @else
        <span class="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-600 text-2xl font-bold text-white ring-4 ring-indigo-500/20">{{ $u->initials() }}</span>
    @endif

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-2xl font-bold">{{ $u->name }}</h1>
        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-500 dark:text-gray-400">
            <span>🗓 Member for {{ $u->membershipDuration() }}</span>
            <span dir="ltr">📱 {{ $u->phone }}</span>
            @if($u->parent_phone)<span dir="ltr">👪 {{ $u->parent_phone }}</span>@endif
            <span>📍 {{ $u->governorate }}</span>
            @if($u->grade_level)<span>🎓 {{ $u->grade_level->label() }}</span>@endif
        </div>
    </div>

    <a href="{{ route('profile.edit') }}" class="btn-secondary">Edit Profile</a>
</div>

{{-- ── Circular progress + quick stats ────────────────────────── --}}
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="card flex flex-col items-center">
        <div class="relative h-24 w-24">
            <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90">
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" class="stroke-gray-200 dark:stroke-gray-800"/>
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" stroke-linecap="round"
                        class="stroke-indigo-600 dark:stroke-indigo-400"
                        stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                        stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $stats['exams_percent'] / 100) }}"/>
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-lg font-bold">{{ $stats['exams_percent'] }}%</span>
        </div>
        <div class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">Exams Passed<br><span class="font-semibold text-gray-700 dark:text-gray-200">{{ $stats['exams_passed'] }}/{{ $stats['exams_total'] }}</span></div>
    </div>

    <div class="card flex flex-col items-center">
        <div class="relative h-24 w-24">
            <svg viewBox="0 0 100 100" class="h-full w-full -rotate-90">
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" class="stroke-gray-200 dark:stroke-gray-800"/>
                <circle cx="50" cy="50" r="42" fill="none" stroke-width="10" stroke-linecap="round"
                        class="stroke-green-500"
                        stroke-dasharray="{{ 2 * 3.14159 * 42 }}"
                        stroke-dashoffset="{{ 2 * 3.14159 * 42 * (1 - $stats['lessons_percent'] / 100) }}"/>
            </svg>
            <span class="absolute inset-0 flex items-center justify-center text-lg font-bold">{{ $stats['lessons_percent'] }}%</span>
        </div>
        <div class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">Lessons Watched<br><span class="font-semibold text-gray-700 dark:text-gray-200">{{ $stats['completed_lessons'] }}/{{ $stats['total_lessons'] }}</span></div>
    </div>

    <div class="card"><div class="text-3xl font-bold text-amber-500">{{ $stats['avg_quiz_score'] }}%</div><div class="text-sm text-gray-500 dark:text-gray-400">Avg Quiz Score</div></div>
    <div class="card"><div class="text-3xl font-bold text-sky-500">{{ $stats['watch_minutes'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Minutes Watched</div></div>
</div>

{{-- ── Average Grades chart (weekly / monthly) ────────────────── --}}
<div class="card mb-6" x-data="gradesChart">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold">Average Grades</h2>
        <div class="flex gap-1 rounded-lg bg-gray-100 p-1 text-xs font-semibold dark:bg-gray-800">
            <button type="button" @click="setRange('weekly')" :class="range === 'weekly' ? 'bg-white shadow dark:bg-gray-700' : 'text-gray-400'" class="rounded-md px-3 py-1">Weekly</button>
            <button type="button" @click="setRange('monthly')" :class="range === 'monthly' ? 'bg-white shadow dark:bg-gray-700' : 'text-gray-400'" class="rounded-md px-3 py-1">Monthly</button>
        </div>
    </div>

    <div class="flex h-48 items-end gap-1">
        <template x-for="(value, i) in current.data" :key="range + i">
            <div class="group relative flex-1">
                <div class="w-full rounded-t bg-indigo-500/80 transition-all group-hover:bg-indigo-400"
                     :style="'height: ' + (value === null ? 2 : Math.max(4, value)) + '%'"
                     :title="current.labels[i] + (value === null ? ': no exams' : ': ' + value + '%')"></div>
            </div>
        </template>
    </div>
    <div class="mt-2 flex justify-between text-xs text-gray-400">
        <span x-text="current.labels[0]"></span>
        <span x-text="current.labels[current.labels.length - 1]"></span>
    </div>
    <p class="mt-2 text-xs text-gray-400">Average quiz score per day. Gaps = days with no exam attempts.</p>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('gradesChart', () => ({
            range: 'weekly',
            series: @json($chart),

            get current() {
                return this.series[this.range];
            },

            setRange(range) {
                this.range = range;
            },
        }));
    });
</script>

{{-- ── Continue learning ──────────────────────────────────────── --}}
<h2 class="mb-4 font-semibold">Continue Learning</h2>
<div class="mb-6 grid gap-4 md:grid-cols-2">
    @forelse($enrollments as $enrollment)
        @php($progress = $enrollment->course->progressFor($u))
        <a href="{{ route('courses.show', $enrollment->course) }}" class="card block hover:border-indigo-500">
            <div class="mb-2 font-semibold">{{ $enrollment->course->title }}</div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $progress }}% complete</div>
        </a>
    @empty
        <div class="card text-sm text-gray-500 dark:text-gray-400">
            You are not enrolled in any course yet. <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('courses.index') }}">Browse courses →</a>
        </div>
    @endforelse
</div>

{{-- ── Invoices / subscriptions ───────────────────────────────── --}}
<h2 class="mb-4 font-semibold">Invoices & Subscriptions</h2>
<div class="card p-0">
    <table class="w-full">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-800">
                <th class="table-th">Course</th>
                <th class="table-th">Reference</th>
                <th class="table-th">Amount</th>
                <th class="table-th">Status</th>
                <th class="table-th">Date</th>
                <th class="table-th"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr class="border-b border-gray-100 dark:border-gray-800/50">
                    <td class="table-td font-medium">{{ $payment->course->title }}</td>
                    <td class="table-td font-mono text-xs">{{ $payment->fawry_reference_number ?? $payment->merchant_ref_number }}</td>
                    <td class="table-td font-mono">{{ number_format($payment->amount, 2) }} EGP</td>
                    <td class="table-td">
                        <span class="badge {{ $payment->isPaid() ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' }}">{{ $payment->status->label() }}</span>
                    </td>
                    <td class="table-td text-gray-400">{{ $payment->created_at->format('Y-m-d') }}</td>
                    <td class="table-td"><a class="text-indigo-600 dark:text-indigo-400" href="{{ route('payments.show', $payment) }}">View</a></td>
                </tr>
            @empty
                <tr><td class="table-td text-gray-400" colspan="6">No invoices yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
