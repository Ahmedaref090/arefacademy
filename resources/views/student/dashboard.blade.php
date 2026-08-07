@extends('layouts.app')
@section('title', __('Home – Aref Academy'))

@section('content')
@php($u = auth()->user())

{{-- ── Profile header ─────────────────────────────────────────── --}}
<div class="card mb-6 flex flex-wrap items-center gap-5">
    @if($u->avatarUrl())
        <img src="{{ $u->avatarUrl() }}" alt="" class="h-20 w-20 rounded-full object-cover ring-4 ring-brand-500/30 shadow-lg">
    @else
        <span class="grid h-20 w-20 place-items-center rounded-full bg-gradient-to-br from-brand-600 to-fuchsia-600 text-2xl font-bold text-white ring-4 ring-brand-500/30 shadow-lg">{{ $u->initials() }}</span>
    @endif

    <div class="min-w-0 flex-1">
        <h1 class="truncate text-3xl font-extrabold">{{ $u->name }}</h1>
        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1.5 text-sm text-gray-500 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5"><x-icon name="calendar" class="h-4 w-4 text-brand-400"/> {{ __('Member for :duration', ['duration' => $u->membershipDuration()]) }}</span>
            <span class="inline-flex items-center gap-1.5 font-mono" dir="ltr"><x-icon name="phone" class="h-4 w-4 text-brand-400"/> {{ $u->phone }}</span>
            @if($u->parent_phone)<span class="inline-flex items-center gap-1.5 font-mono" dir="ltr"><x-icon name="users" class="h-4 w-4 text-brand-400"/> {{ $u->parent_phone }}</span>@endif
            <span class="inline-flex items-center gap-1.5"><x-icon name="map" class="h-4 w-4 text-brand-400"/> {{ $u->governorate }}</span>
            @if($u->grade_level)<span class="inline-flex items-center gap-1.5"><x-icon name="trophy" class="h-4 w-4 text-brand-400"/> {{ $u->grade_level->label() }}</span>@endif
        </div>
    </div>

    <a href="{{ route('profile.edit') }}" class="btn"><x-icon name="edit" class="h-4 w-4" :stroke="2"/> {{ __('Edit Profile') }}</a>
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
        <div class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Exams Passed') }}<br><span class="font-semibold text-gray-700 dark:text-gray-200">{{ $stats['exams_passed'] }}/{{ $stats['exams_total'] }}</span></div>
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
        <div class="mt-2 text-center text-sm text-gray-500 dark:text-gray-400">{{ __('Lessons Watched') }}<br><span class="font-semibold text-gray-700 dark:text-gray-200">{{ $stats['completed_lessons'] }}/{{ $stats['total_lessons'] }}</span></div>
    </div>

    <div class="card"><div class="text-3xl font-bold text-amber-500">{{ $stats['avg_quiz_score'] }}%</div><div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Avg Quiz Score') }}</div></div>
    <div class="card"><div class="text-3xl font-bold text-sky-500">{{ $stats['watch_minutes'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">{{ __('Minutes Watched') }}</div></div>
</div>

{{-- ── Average Grades chart (weekly / monthly) ────────────────── --}}
<div class="card mb-6" x-data="gradesChart">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold">{{ __('Average Grades') }}</h2>
        <div class="flex gap-1 rounded-lg bg-gray-100 p-1 text-xs font-semibold dark:bg-gray-800">
            <button type="button" @click="setRange('weekly')" :class="range === 'weekly' ? 'bg-white shadow dark:bg-gray-700' : 'text-gray-400'" class="rounded-md px-3 py-1">{{ __('Weekly') }}</button>
            <button type="button" @click="setRange('monthly')" :class="range === 'monthly' ? 'bg-white shadow dark:bg-gray-700' : 'text-gray-400'" class="rounded-md px-3 py-1">{{ __('Monthly') }}</button>
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
    <p class="mt-2 text-xs text-gray-400">{{ __('Average quiz score per day. Gaps = days with no exam attempts.') }}</p>
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
<h2 class="mb-4 font-semibold">{{ __('Continue Learning') }}</h2>
<div class="mb-6 grid gap-4 md:grid-cols-2">
    @forelse($enrollments as $enrollment)
        @php($progress = $enrollment->course->progressFor($u))
        <a href="{{ route('courses.show', $enrollment->course) }}" class="card block hover:border-indigo-500">
            <div class="mb-2 font-semibold">{{ $enrollment->course->title }}</div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
            </div>
            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __(':percent% complete', ['percent' => $progress]) }}</div>
        </a>
    @empty
        <div class="card text-sm text-gray-500 dark:text-gray-400">
            {{ __('You are not enrolled in any course yet.') }} <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('courses.index') }}">{{ __('Browse courses →') }}</a>
        </div>
    @endforelse
</div>

{{-- ── Invoices / subscriptions ───────────────────────────────── --}}
<h2 class="mb-4 font-semibold">{{ __('Invoices & Subscriptions') }}</h2>
<div x-data="{ active: null }" @keydown.escape.window="active = null">
    <div class="card p-0">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">{{ __('Course') }}</th>
                    <th class="table-th">{{ __('Amount') }}</th>
                    <th class="table-th">{{ __('Date') }}</th>
                    <th class="table-th">{{ __('Payment Status') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-b border-gray-100 last:border-0 dark:border-gray-800/50">
                        <td class="table-td font-medium">
                            {{ $payment->course->title }}
                            @php($displayMonths = $payment->displayMonths())
                            @if($displayMonths->isNotEmpty())
                                <div class="mt-1 text-xs font-normal text-gray-500 dark:text-gray-400">
                                    @foreach($displayMonths as $month)
                                        <span class="mr-1 inline-block rounded-full bg-indigo-100 px-2 py-0.5 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $month->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="table-td font-mono">{{ number_format($payment->amount, 2) }} {{ __('EGP') }}</td>
                        <td class="table-td text-gray-400">{{ $payment->created_at?->format('Y-m-d') }}</td>
                        <td class="table-td">
                            <button type="button" @click="active = {{ $payment->id }}"
                                class="inline-flex cursor-pointer items-center gap-2 rounded-full px-3 py-1 text-xs font-bold transition-transform duration-200 ease-in-out hover:scale-105 {{ $payment->isPending() ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300' : ($payment->isApproved() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                                <span class="h-2 w-2 rounded-full {{ $payment->isPending() ? 'animate-pulse bg-amber-500' : ($payment->isApproved() ? 'bg-emerald-500' : 'bg-red-500') }}"></span>
                                {{ $payment->isPending() ? __('Pending') : ($payment->isApproved() ? __('Approved') : __('Rejected')) }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">{{ __('No invoices yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Payment details modal --}}
    <template x-teleport="body">
        <div x-show="active !== null" x-cloak
            class="fixed inset-0 z-50 flex items-end justify-center bg-gray-950/60 p-4 backdrop-blur-sm sm:items-center"
            @click.self="active = null">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/10 dark:bg-gray-900 dark:ring-white/10">
                {{-- Modal header --}}
                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-800 dark:bg-gray-900">
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ __('Payment Status') }}</h3>
                    <button type="button" @click="active = null" class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition-colors hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                    </button>
                </div>

                {{-- Payment detail cards --}}
                @foreach($payments as $payment)
                    <div x-show="active === {{ $payment->id }}" x-cloak class="px-6 py-5">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Course') }}</p>
                                <p class="truncate font-bold text-gray-900 dark:text-white">{{ $payment->course->title }}</p>
                            </div>
                            <div class="text-end">
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('Amount') }}</p>
                                <p class="font-mono text-lg font-bold text-gray-900 dark:text-white">{{ number_format($payment->amount, 2) }} <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ __('EGP') }}</span></p>
                            </div>
                        </div>

                        <div class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                            @php($displayMonths = $payment->displayMonths())
                            @if($displayMonths->isNotEmpty())
                                <div class="flex items-start justify-between gap-3 text-sm">
                                    <span class="text-gray-500 dark:text-gray-400">{{ __('Months (:count)', ['count' => $displayMonths->count()]) }}</span>
                                    <span class="text-end font-semibold text-gray-900 dark:text-white">
                                        @foreach($displayMonths as $month)
                                            <span class="block">{{ $month->name }}</span>
                                        @endforeach
                                    </span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Payment Method') }}</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $payment->payment_method?->label() ?? '—' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Sender Number / Account') }}</span>
                                <span class="font-mono font-semibold text-gray-900 dark:text-white" dir="ltr">{{ $payment->sender_details }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">{{ __('Status') }}</span>
                                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold {{ $payment->isPending() ? 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300' : ($payment->isApproved() ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400') }}">
                                    <span class="h-2 w-2 rounded-full {{ $payment->isPending() ? 'animate-pulse bg-amber-500' : ($payment->isApproved() ? 'bg-emerald-500' : 'bg-red-500') }}"></span>
                                    {{ $payment->isPending() ? __('Pending') : ($payment->isApproved() ? __('Approved') : __('Rejected')) }}
                                </span>
                            </div>
                        </div>

                        @if($payment->receipt_image_path)
                            <div class="mt-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                                <p class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Attached Receipt') }}</p>
                                @php($receiptUrl = route('payments.receipt', $payment))
                                <a href="{{ $receiptUrl }}" target="_blank" class="group block overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                    <img src="{{ $receiptUrl }}" alt="{{ __('Receipt') }}" class="max-h-48 w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]">
                                </a>
                                <a href="{{ $receiptUrl }}" target="_blank" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-brand-600 hover:text-brand-500 dark:text-brand-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                    {{ __('View Receipt') }}
                                </a>
                            </div>
                        @endif

                        <div class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                            <button type="button" @click="active = null" class="btn-secondary">{{ __('Close') }}</button>
                            <a href="{{ route('payments.show', $payment) }}" class="btn">{{ __('View Full Page') }}</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </template>
</div>
@endsection
