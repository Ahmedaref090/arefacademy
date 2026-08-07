@extends('layouts.app')
@php
    // Clean, trimmed description for SEO meta tags (strip tags + clamp length).
    $seoDescription = mb_substr(strip_tags((string) $course->description), 0, 160);
@endphp
@section('title', $course->title . ' – ' . __('Aref Academy'))
@section('meta_description', $seoDescription)
@section('meta_keywords', $course->title)
@section('canonical', route('courses.show', $course))
@section('url_ar', url(route('courses.show', $course, false)))
@section('url_en', url(route('courses.show', $course, false)))
@section('og_type', 'product')
@section('og_image', $course->thumbnailUrl() ?? asset('images/og-default.jpg'))
@section('twitter_site', '@arefacademy')

@section('content')
@php
    $isPerMonth = $course->isPerMonth();
    $lessonCount = $isPerMonth
        ? $course->months->sum(fn ($month) => $month->lessons->count())
        : $course->lessons->count();
@endphp

{{-- ── Hero: featured course card ─────────────────────────────── --}}
<div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 via-indigo-500 to-purple-600 shadow-xl shadow-indigo-500/20">
    <div class="pointer-events-none absolute -left-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-10 h-64 w-64 rounded-full bg-purple-300/20 blur-3xl"></div>

    <div class="relative flex flex-col gap-6 p-6 sm:p-8 lg:flex-row lg:items-center lg:justify-between">
        {{-- Left: course info --}}
        <div class="min-w-0 flex-1 text-white">
            <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-medium">
                <span class="inline-flex items-center gap-1 rounded-full bg-white/15 px-3 py-1 backdrop-blur">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                    {{ __(':count lessons', ['count' => $lessonCount]) }}
                </span>
                @if($course->grade_level)
                    <span class="rounded-full bg-white/15 px-3 py-1 backdrop-blur">{{ $course->grade_level->label() }}</span>
                @endif
                @if($course->duration_weeks)
                    <span class="rounded-full bg-white/15 px-3 py-1 backdrop-blur">{{ __(':count weeks', ['count' => $course->duration_weeks]) }}</span>
                @endif
            </div>
            <h1 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $course->title }}</h1>
            <p class="mt-2 text-sm text-indigo-100">
                {{ $isPerMonth ? __('Subscribe month by month and learn at your own pace.') : __('Full access to every lesson with a single enrollment.') }}
            </p>
        </div>

        {{-- Right: pricing / subscribe panel --}}
        <div class="w-full shrink-0 lg:w-80">
            <div class="rounded-2xl border border-white/20 bg-white/10 p-5 backdrop-blur-md">
                <div class="text-xs font-medium uppercase tracking-wide text-indigo-100">
                    {{ $isPerMonth ? __('Price / month') : __('Full course') }}
                </div>
                <div class="mt-1 flex items-baseline gap-2">
                    @if($course->hasDiscount())
                        <span class="font-mono text-sm text-indigo-200 line-through">{{ number_format((float) $course->price, 2) }} {{ __('EGP') }}</span>
                        <span class="text-3xl font-extrabold text-white">
                            {{ $course->effectivePrice() > 0 ? number_format($course->effectivePrice(), 2) . ' ' . __('EGP') : __('Free') }}
                        </span>
                    @else
                        <span class="text-3xl font-extrabold text-white">
                            {{ $course->effectivePrice() > 0 ? number_format($course->effectivePrice(), 2) . ' ' . __('EGP') : __('Free') }}
                        </span>
                    @endif
                </div>

                @if($isPerMonth)
                    {{-- Per-month course: pick one or MORE months; price is reactive. --}}
                    @if($availableMonths->isNotEmpty())
                        <form method="POST" action="{{ route('enrollments.store') }}" class="mt-4 space-y-3"
                            x-data="monthSelector()"
                            x-init='init(@json($availableMonths->map(fn ($m) => ['id' => $m->id, 'name' => $m->name])->values()->all(), JSON_UNESCAPED_UNICODE), @js($course->effectivePrice()))'>
                            @csrf
                            <input type="hidden" name="course_id" value="{{ $course->id }}">

                            <div class="space-y-2">
                                <template x-for="m in months" :key="m.id">
                                    <label class="flex cursor-pointer items-center justify-between gap-2 rounded-xl bg-white/95 px-3 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition-colors hover:bg-white/80"
                                        :class="selected.includes(m.id) ? 'ring-2 ring-white/70' : ''">
                                        <span class="flex min-w-0 items-center gap-2">
                                            <input type="checkbox" :value="m.id"
                                                @change="toggle(m.id)"
                                                :checked="selected.includes(m.id)"
                                                name="course_month_ids[]"
                                                class="h-4 w-4 rounded border-indigo-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="truncate" x-text="m.name"></span>
                                        </span>
                                        <span class="shrink-0 font-mono text-xs text-gray-500" x-text="priceText"></span>
                                    </label>
                                </template>
                            </div>

                            {{-- Reactive total --}}
                            <div x-show="selected.length > 0" x-cloak x-transition
                                class="rounded-xl bg-white/95 p-3 text-sm shadow-sm">
                                <div class="flex items-center justify-between text-xs text-gray-500">
                                    <span>{{ __('Selected months') }}</span>
                                    <span><span x-text="selected.length"></span> × <span x-text="priceText"></span></span>
                                </div>
                                <div class="mt-1 flex items-center justify-between font-bold text-indigo-800">
                                    <span>{{ __('Total') }}</span>
                                    <span class="font-mono text-lg" x-text="totalText"></span>
                                </div>
                            </div>

                            <button type="submit"
                                class="w-full rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-indigo-700 shadow-md transition-all duration-300 ease-in-out hover:scale-[1.03] hover:shadow-xl"
                                :disabled="selected.length === 0"
                                :class="selected.length === 0 ? 'cursor-not-allowed opacity-60' : ''">
                                {{ __('Subscribe to Month') }}
                            </button>

                            <p x-show="selected.length === 0" class="text-center text-xs text-indigo-100">
                                {{ __('Choose at least one month to continue.') }}
                            </p>
                            @error('course_month_ids')
                                <div class="text-xs text-rose-200">{{ $message }}</div>
                            @enderror
                        </form>
                    @else
                        <div class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-400/20 px-4 py-3 text-sm font-medium text-emerald-50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            {{ __("You've requested all available months.") }}
                        </div>
                    @endif
                @elseif(! $hasActiveSubscription)
                    {{-- Lifetime course: standard full-course enrollment. --}}
                    <a href="{{ route('payments.checkout', $course) }}"
                        class="mt-4 block w-full rounded-xl bg-white px-4 py-2.5 text-center text-sm font-bold text-indigo-700 shadow-md transition-all duration-300 ease-in-out hover:scale-[1.03] hover:shadow-xl">
                        {{ $enrollment?->isExpired() ? __('Renew Subscription') : __('Enroll in Full Course') }}
                    </a>
                @else
                    <div class="mt-4 flex items-center gap-2 rounded-xl bg-emerald-400/20 px-4 py-3 text-sm font-medium text-emerald-50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ __("You're subscribed — enjoy the course!") }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if($enrollment && $enrollment->isExpired())
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm shadow-sm dark:border-rose-500/20 dark:bg-rose-500/10" dir="rtl">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-rose-100 text-rose-500 dark:bg-rose-500/20 dark:text-rose-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
            </span>
            <div>
                <span class="font-semibold text-rose-700 dark:text-rose-300">{{ __('Your subscription to this course has expired.') }}</span>
                <span class="text-rose-600/80 dark:text-rose-300/70">
                    {{ __('Your access expired on :date. Renew your subscription to continue lessons and quizzes.', ['date' => $enrollment->expires_at->format('Y-m-d')]) }}
                </span>
            </div>
        </div>
        <a href="{{ route('payments.checkout', $course) }}"
            class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-in-out hover:scale-[1.03] hover:bg-rose-500">{{ __('Renew Subscription') }}</a>
    </div>
@endif

{{-- Pending payment: soft amber toast --}}
@if($pendingPayment)
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm shadow-sm dark:border-amber-500/20 dark:bg-amber-500/10">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </span>
            <div>
                <div class="font-semibold text-amber-800 dark:text-amber-300">{{ __('Payment under review') }}</div>
                <div class="text-amber-700/80 dark:text-amber-300/70">
                    {{ __('You have a pending payment.') }}
                </div>
            </div>
        </div>
        <a class="rounded-xl bg-amber-500/10 px-4 py-2 font-semibold text-amber-700 transition-all duration-300 ease-in-out hover:bg-amber-500/20 dark:text-amber-300"
            href="{{ route('payments.show', $pendingPayment) }}">{{ __('View details →') }}</a>
    </div>
@endif

{{-- ── WhatsApp group ─────────────────────────────────────────── --}}
@if($hasActiveSubscription && $course->whatsapp_group_link)
    <a href="{{ $course->whatsapp_group_link }}" target="_blank" rel="noopener noreferrer"
        class="group mb-6 flex items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-gradient-to-r from-emerald-50 to-teal-50 px-5 py-4 shadow-sm transition-all duration-300 ease-in-out hover:-translate-y-[2px] hover:shadow-lg dark:border-emerald-500/20 dark:from-emerald-500/10 dark:to-teal-500/10">
        <div class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-500 text-xl text-white shadow-md shadow-emerald-500/30 transition-transform duration-300 group-hover:scale-110">💬</span>
            <div>
                <div class="font-semibold text-emerald-700 dark:text-emerald-400">{{ __('Join the WhatsApp Group') }}</div>
                <div class="text-xs text-emerald-600/80 dark:text-emerald-400/70">{{ __('Exclusive for subscribed students — announcements & support') }}</div>
            </div>
        </div>
        <span class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-300 ease-in-out group-hover:scale-105 group-hover:bg-emerald-500">{{ __('Join →') }}</span>
    </a>
@endif

{{-- ── About ──────────────────────────────────────────────────── --}}
@if($course->description)
    <div class="mb-8 rounded-2xl border border-gray-100 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <h2 class="mb-2 flex items-center gap-2 font-semibold text-gray-900 dark:text-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5 text-indigo-500"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
            {{ __('About this course') }}
        </h2>
        <div class="text-sm leading-relaxed text-gray-600 dark:text-gray-300">{!! nl2br(e($course->description)) !!}</div>
    </div>
@endif

{{-- ── Course content ─────────────────────────────────────────── --}}
<div class="mb-4 flex items-center gap-2">
    <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
    </span>
    <h2 class="text-lg font-bold">{{ __('Course Content') }}</h2>
    <span class="text-sm text-gray-400">· {{ __(':count lessons', ['count' => $lessonCount]) }}</span>
</div>

@if($isPerMonth)
    {{-- Per-month course: each month is an accordion card. --}}
    <div class="space-y-4">
        @forelse($course->months as $month)
            @php
                $monthApproved = $hasActiveSubscription || in_array($month->id, $approvedMonthIds);
                $isOpen = $monthApproved || $loop->first;
            @endphp
            <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition-all duration-300 ease-in-out hover:-translate-y-[3px] hover:shadow-lg dark:border-gray-800 dark:bg-gray-900">
                <button type="button" data-accordion-toggle
                    class="flex w-full items-center justify-between gap-3 px-5 py-4 text-left transition-colors duration-200 hover:bg-gray-50/80 dark:hover:bg-gray-800/50">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $monthApproved ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-indigo-50 text-indigo-500 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h3 class="truncate font-bold text-gray-900 dark:text-gray-100">{{ $month->name }}</h3>
                            <p class="text-xs text-gray-400">{{ $month->lessons->count() }} {{ \Illuminate\Support\Str::plural('lesson', $month->lessons->count()) }}</p>
                        </div>
                    </div>
                    <div class="flex shrink-0 items-center gap-3">
                        @if($monthApproved)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ __('Subscribed') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                {{ __('Locked') }}
                            </span>
                        @endif
                        <span data-chevron class="text-gray-400 transition-transform duration-300 ease-in-out {{ $isOpen ? 'rotate-180' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </span>
                    </div>
                </button>
                <div data-accordion-body class="grid transition-all duration-300 ease-in-out {{ $isOpen ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]' }}">
                    <div class="overflow-hidden">
                        <div class="space-y-1 border-t border-gray-100 px-3 py-3 dark:border-gray-800">
                            @forelse($month->lessons as $lesson)
                                @php($locked = ! $monthApproved && ! $lesson->is_free)
                                <div class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition-colors duration-200 {{ $locked ? '' : 'hover:bg-indigo-50/60 dark:hover:bg-gray-800/60' }}">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $locked ? 'bg-gray-100 text-gray-400 dark:bg-gray-800' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                                            @if($locked)
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            @if($locked)
                                                <span class="block truncate text-sm text-gray-400 dark:text-gray-500">{{ $lesson->title }}</span>
                                            @else
                                                <a class="block truncate text-sm font-medium text-gray-700 transition-colors duration-200 hover:text-indigo-600 dark:text-gray-200 dark:hover:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                                            @endif
                                            @if($lesson->is_free)
                                                <span class="mt-0.5 inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-medium text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">{{ __('Free preview') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    @if($lesson->duration_minutes)
                                        <span class="flex shrink-0 items-center gap-1 text-xs text-gray-400">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                            {{ $lesson->duration_minutes }} {{ __('min') }}
                                        </span>
                                    @endif
                                </div>
                            @empty
                                <div class="px-3 py-4 text-sm text-gray-400">{{ __('No lessons in this month yet.') }}</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-5 py-10 text-center text-sm text-gray-400 dark:border-gray-800 dark:bg-gray-900">{{ __('No months available yet.') }}</div>
        @endforelse
    </div>
@else
    {{-- Lifetime course: flat list of all lessons. --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-3 shadow-sm transition-all duration-300 ease-in-out hover:shadow-md dark:border-gray-800 dark:bg-gray-900">
        @forelse($course->lessons as $i => $lesson)
            @php($locked = ! $hasActiveSubscription && ! $lesson->is_free)
            <div class="flex items-center justify-between gap-3 rounded-xl px-3 py-2.5 transition-colors duration-200 {{ $locked ? '' : 'hover:bg-indigo-50/60 dark:hover:bg-gray-800/60' }}">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $locked ? 'bg-gray-100 text-gray-400 dark:bg-gray-800' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' }}">
                        @if($locked)
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        @if($locked)
                            <span class="block truncate text-sm text-gray-400 dark:text-gray-500">{{ $lesson->title }}</span>
                        @else
                            <a class="block truncate text-sm font-medium text-gray-700 transition-colors duration-200 hover:text-indigo-600 dark:text-gray-200 dark:hover:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                        @endif
                        @if($lesson->is_free)
                            <span class="mt-0.5 inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-medium text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">{{ __('Free preview') }}</span>
                        @endif
                    </div>
                </div>
                @if($lesson->duration_minutes)
                    <span class="flex shrink-0 items-center gap-1 text-xs text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ $lesson->duration_minutes }} {{ __('min') }}
                    </span>
                @endif
            </div>
        @empty
            <div class="px-3 py-4 text-sm text-gray-400">{{ __('No lessons yet.') }}</div>
        @endforelse
    </div>
@endif

{{-- Accordion toggle: smooth slide via grid-rows 0fr → 1fr + chevron rotation --}}
<script>
    document.querySelectorAll('[data-accordion-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var body = toggle.parentElement.querySelector('[data-accordion-body]');
            var chevron = toggle.querySelector('[data-chevron]');
            if (! body) return;

            var isOpen = body.classList.contains('grid-rows-[1fr]');
            body.classList.toggle('grid-rows-[1fr]', ! isOpen);
            body.classList.toggle('grid-rows-[0fr]', isOpen);
            if (chevron) {
                chevron.classList.toggle('rotate-180', ! isOpen);
            }
        });
    });
</script>

{{-- Reactive multi-month price selector --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('monthSelector', () => ({
            months: [],
            price: 0,
            selected: [],
            init(months, price) {
                this.months = months;
                this.price = price;
            },
            toggle(id) {
                const i = this.selected.indexOf(id);
                if (i === -1) {
                    this.selected.push(id);
                } else {
                    this.selected.splice(i, 1);
                }
            },
            format(value) {
                return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            },
            get priceText() {
                return this.format(this.price) + ' EGP';
            },
            get totalText() {
                return this.format(this.price * this.selected.length) + ' EGP';
            },
        }));
    });
</script>
@endsection
