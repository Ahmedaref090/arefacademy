@extends('layouts.app')
@section('title', $course->title . ' – Aref Academy')

@section('content')
@php
    $isPerMonth = $course->isPerMonth();
    $lessonCount = $isPerMonth
        ? $course->months->sum(fn ($month) => $month->lessons->count())
        : $course->lessons->count();
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">{{ $course->title }}</h1>
        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $lessonCount }} lessons
            @if($course->grade_level) · {{ $course->grade_level->label() }} @endif
            @if($course->duration_weeks) · {{ $course->duration_weeks }} weeks @endif
        </div>
    </div>
    <div class="text-right">
        <div class="font-mono text-xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ (float) $course->price > 0 ? number_format($course->price, 2) . ' EGP' : 'Free' }}
        </div>

        @if($isPerMonth)
            {{-- Per-month course: the student must pick WHICH month to subscribe to. --}}
            @if($availableMonths->isNotEmpty())
                <form method="POST" action="{{ route('enrollments.store') }}" class="mt-2 flex flex-col items-end gap-2">
                    @csrf
                    <input type="hidden" name="course_id" value="{{ $course->id }}">
                    <select name="course_month_id" required
                        class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <option value="" disabled @selected(! old('course_month_id'))>Select a month…</option>
                        @foreach($availableMonths as $month)
                            <option value="{{ $month->id }}" @selected(old('course_month_id') == $month->id)>{{ $month->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn">Subscribe to Month</button>
                    @error('course_month_id')
                        <div class="text-xs text-red-600 dark:text-red-400">{{ $message }}</div>
                    @enderror
                </form>
            @else
                <div class="mt-2 text-sm text-emerald-600 dark:text-emerald-400">✓ You have requested all available months.</div>
            @endif
        @elseif(! $hasActiveSubscription)
            {{-- Lifetime course: standard full-course enrollment. --}}
            <a href="{{ route('payments.checkout', $course) }}" class="btn mt-2">
                {{ $enrollment?->isExpired() ? 'جدد الاشتراك' : 'Enroll in Full Course' }}
            </a>
        @endif
    </div>
</div>

@if(session('status'))
    <div class="card mb-6 border-indigo-500 text-sm">{{ session('status') }}</div>
@endif

@if(session('error'))
    <div class="card mb-6 border-red-500 text-sm text-red-600 dark:text-red-400" dir="auto">{{ session('error') }}</div>
@endif

@if($enrollment && $enrollment->isExpired())
    <div class="card mb-6 border-red-500 text-sm" dir="rtl">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <span class="font-semibold text-red-600 dark:text-red-400">🔒 انتهى اشتراكك في هذا الكورس.</span>
                <span class="text-gray-500 dark:text-gray-400">
                    انتهت صلاحية وصولك في {{ $enrollment->expires_at->format('Y-m-d') }}. جدد اشتراكك لمتابعة الدروس والاختبارات.
                </span>
            </div>
            <a href="{{ route('payments.checkout', $course) }}" class="btn">جدد اشتراكك</a>
        </div>
    </div>
@endif

@if($pendingPayment)
    <div class="card mb-6 border-amber-500 text-sm">
        ⏳ You have a pending payment.
        @if($pendingPayment->fawry_reference_number)
            Fawry reference: <span class="font-mono font-bold">{{ $pendingPayment->fawry_reference_number }}</span>
        @endif
        <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('payments.show', $pendingPayment) }}">View details →</a>
    </div>
@endif

@if($hasActiveSubscription && $course->whatsapp_group_link)
    <a href="{{ $course->whatsapp_group_link }}" target="_blank" rel="noopener noreferrer"
        class="card mb-6 flex items-center justify-between gap-3 border-emerald-500 bg-emerald-50 transition hover:bg-emerald-100 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20">
        <div class="flex items-center gap-3">
            <span class="text-2xl">💬</span>
            <div>
                <div class="font-semibold text-emerald-700 dark:text-emerald-400">Join the WhatsApp Group</div>
                <div class="text-xs text-emerald-600/80 dark:text-emerald-400/70">Exclusive for subscribed students — announcements &amp; support</div>
            </div>
        </div>
        <span class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Join →</span>
    </a>
@endif

@if($course->description)
    <div class="card mb-6 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{!! nl2br(e($course->description)) !!}</div>
@endif

<h2 class="mb-3 font-semibold">Course Content</h2>

@if($isPerMonth)
    {{-- Per-month course: lessons grouped under their month. --}}
    <div class="space-y-4">
        @forelse($course->months as $month)
            @php($monthApproved = $hasActiveSubscription || in_array($month->id, $approvedMonthIds))
            <div class="card overflow-hidden p-0">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 dark:border-gray-800 dark:bg-gray-800/40">
                    <h3 class="font-semibold">{{ $month->name }}</h3>
                    @if($monthApproved)
                        <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Subscribed</span>
                    @else
                        <span class="badge bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">🔒 Locked</span>
                    @endif
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($month->lessons as $i => $lesson)
                        @php($locked = ! $monthApproved && ! $lesson->is_free)
                        <div class="flex items-center justify-between gap-3 px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-xs text-gray-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                @if($locked)
                                    <span class="text-gray-400 dark:text-gray-500">🔒 {{ $lesson->title }}</span>
                                @else
                                    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                                @endif
                                @if($lesson->is_free)<span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">Free preview</span>@endif
                            </div>
                            <span class="text-xs text-gray-400">{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' min' : '' }}</span>
                        </div>
                    @empty
                        <div class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">No lessons in this month yet.</div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="card px-5 py-4 text-sm text-gray-500 dark:text-gray-400">No months available yet.</div>
        @endforelse
    </div>
@else
    {{-- Lifetime course: flat list of all lessons. --}}
    <div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
        @forelse($course->lessons as $i => $lesson)
            @php($locked = ! $hasActiveSubscription && ! $lesson->is_free)
            <div class="flex items-center justify-between gap-3 px-5 py-3">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    @if($locked)
                        <span class="text-gray-400 dark:text-gray-500">🔒 {{ $lesson->title }}</span>
                    @else
                        <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                    @endif
                    @if($lesson->is_free)<span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">Free preview</span>@endif
                </div>
                <span class="text-xs text-gray-400">{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' min' : '' }}</span>
            </div>
        @empty
            <div class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">No lessons yet.</div>
        @endforelse
    </div>
@endif
@endsection
