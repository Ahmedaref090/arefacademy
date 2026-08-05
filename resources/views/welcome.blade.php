@extends('layouts.landing')
@section('title', __('Aref in Programming') . ' – ' . __('Learn programming the right way.'))

@section('content')
@php
    // Group courses by grade level (enum-cast, plain string, or none).
    // Tabs are only shown when at least one course has a real grade level.
    $gradeKey = function ($course) {
        $g = $course->grade_level ?? null;
        if ($g instanceof \App\Enums\GradeLevel) {
            return $g->value;
        }
        return is_string($g) && $g !== '' ? $g : 'general';
    };
    $gradeLabel = function ($course) {
        $g = $course->grade_level ?? null;
        return $g instanceof \App\Enums\GradeLevel ? $g->label() : __('General');
    };
    $grouped = $courses->groupBy($gradeKey);
    $showTabs = $courses->isNotEmpty() && $grouped->keys()->contains(fn ($k) => $k !== 'general');

    // Stats are computed (and cached) in HomeController.
    $statsData = [
        ['icon' => '🎓', 'value' => $stats['students'] ?? 0, 'label' => __('Students on the platform')],
        ['icon' => '📚', 'value' => $stats['courses'] ?? $courses->count(), 'label' => __('Courses')],
        ['icon' => '🚀', 'value' => $stats['enrollments'] ?? 0, 'label' => __('Enrollments')],
    ];

    $faqs = [
        ['q' => __('Is the content explained in Arabic?'), 'a' => __('Yes, all lessons are explained in simple Arabic with practical examples and real projects.')],
        ['q' => __('How do I subscribe to a course?'), 'a' => __('Create your account, choose the course, send the payment, and your subscription will be activated right after review.')],
        ['q' => __('Can I watch the lessons from my phone?'), 'a' => __('Sure! The platform works smoothly on phones, tablets, and computers.')],
        ['q' => __('Are there quizzes and assignments?'), 'a' => __('Every course has quizzes and assignments to help you test yourself and track your progress step by step.')],
    ];
@endphp

{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-50 via-white to-gold-50 dark:from-slate-950 dark:via-slate-950 dark:to-brand-950"></div>
    <div class="absolute -top-24 -start-24 -z-10 h-72 w-72 rounded-full bg-brand-400/20 blur-3xl"></div>
    <div class="absolute -bottom-24 -end-24 -z-10 h-72 w-72 rounded-full bg-gold-400/20 blur-3xl"></div>

    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-24">
        <div data-reveal>
            <span class="badge bg-brand-600/10 text-brand-700 dark:bg-gold-500/10 dark:text-gold-400">⚡ {{ __('Programming learning platform') }}</span>
            <h1 class="mt-4 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                {{ __('Aref in Programming') }}
            </h1>
            <p class="text-gradient mt-3 text-2xl font-bold sm:text-3xl">
                {{ __('Learn programming the right way.') }}
            </p>
            <p class="mt-4 max-w-lg text-lg text-slate-600 dark:text-slate-300">
                {{ __('Structured courses, real projects, quizzes and assignments — everything you need to go from zero to professional.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-6 py-3 font-semibold text-white shadow-lg shadow-brand-600/30 transition hover:bg-brand-700">{{ __('Get Started') }}</a>
                <a href="#courses" class="rounded-xl border border-slate-300 px-6 py-3 font-semibold transition hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Browse Courses') }}</a>
            </div>
            <ul class="mt-8 flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-500 dark:text-slate-400">
                @foreach([__('Arabic explanations'), __('Hands-on projects'), __('Quizzes & assignments')] as $perk)
                    <li class="flex items-center gap-1.5"><span class="text-gold-500">✔</span> {{ $perk }}</li>
                @endforeach
            </ul>
        </div>

        <div class="relative pb-10" data-reveal>
            <x-landing.code-window />
            {{-- Floating instructor card --}}
            <div class="absolute bottom-0 end-4 flex w-56 items-center gap-3 rounded-2xl border border-slate-200 bg-white/90 p-3 shadow-xl backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:w-64">
                <img src="/images/instructor.png" alt="{{ __('Instructor') }}"
                     class="h-12 w-12 shrink-0 rounded-xl object-cover"
                     onerror="this.src='https://placehold.co/96x96/d32f2f/ffffff?text=A'">
                <div class="min-w-0">
                    <div class="truncate text-sm font-bold">{{ __('Your instructor') }}</div>
                    <div class="truncate text-xs text-slate-500 dark:text-slate-400">{{ __('With you step by step') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Stats --}}
<section class="border-y border-slate-200 bg-white py-10 dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto grid max-w-5xl grid-cols-1 gap-6 px-4 sm:grid-cols-3 sm:px-6 lg:px-8">
        @foreach($statsData as $stat)
            <x-landing.stat :icon="$stat['icon']" :value="$stat['value']" :label="$stat['label']" />
        @endforeach
    </div>
</section>

{{-- Courses (with grade-level tabs) --}}
<section id="courses" class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8" x-data="{ tab: 'all' }">
    <div class="mb-8 text-center" data-reveal>
        <h2 class="text-3xl font-extrabold">{{ __('Available Courses') }}</h2>
        <p class="mt-2 text-slate-500 dark:text-slate-400">{{ __('Pick a course and start learning today.') }}</p>
    </div>

    @if($showTabs)
        <div class="mb-8 flex flex-wrap justify-center gap-2" data-reveal>
            <button type="button" @click="tab = 'all'"
                    :class="tab === 'all' ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 text-slate-600 hover:border-brand-400 dark:border-slate-700 dark:text-slate-300'"
                    class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">{{ __('All') }}</button>
            @foreach($grouped as $key => $group)
                @if($key !== 'general')
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-brand-600 bg-brand-600 text-white' : 'border-slate-300 text-slate-600 hover:border-brand-400 dark:border-slate-700 dark:text-slate-300'"
                            class="rounded-full border px-4 py-1.5 text-sm font-semibold transition">{{ $gradeLabel($group->first()) }}</button>
                @endif
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
        @forelse($courses as $course)
            <x-landing.course-card :course="$course" :grade-key="$gradeKey($course)" />
        @empty
            <p class="col-span-full text-center text-slate-400">{{ __('No courses available yet.') }}</p>
        @endforelse
    </div>
</section>

{{-- How it works --}}
<section class="border-y border-slate-200 bg-white py-16 dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center" data-reveal>
            <h2 class="text-3xl font-extrabold">{{ __('How do I start on the platform?') }}</h2>
            <p class="mt-2 text-slate-500 dark:text-slate-400">{{ __('Four simple steps and you are in.') }}</p>
        </div>
        <div class="relative grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="absolute inset-x-16 top-7 hidden border-t-2 border-dashed border-brand-200 dark:border-brand-800 lg:block"></div>
            @foreach([
                ['icon' => '👤', 'title' => __('Create Account'), 'desc' => __('Register with your phone number in seconds.')],
                ['icon' => '📚', 'title' => __('Select Course'), 'desc' => __('Choose the course that fits your level.')],
                ['icon' => '▶️', 'title' => __('Watch & Learn'), 'desc' => __('Follow the lessons at your own pace.')],
                ['icon' => '🧠', 'title' => __('Test Yourself'), 'desc' => __('Quizzes and assignments to prove your skills.')],
            ] as $i => $step)
                <div class="relative rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-800 dark:bg-slate-950" data-reveal>
                    <div class="relative mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-3xl text-white shadow-lg shadow-brand-600/30">{{ $step['icon'] }}</div>
                    <div class="mb-1 font-mono text-xs font-bold text-gold-500">{{ __('Step') }} {{ $i + 1 }}</div>
                    <h3 class="font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="mx-auto max-w-3xl px-4 py-16 sm:px-6 lg:px-8">
    <div class="mb-10 text-center" data-reveal>
        <h2 class="text-3xl font-extrabold">{{ __('Frequently Asked Questions') }}</h2>
        <p class="mt-2 text-slate-500 dark:text-slate-400">{{ __('Everything you need to know before you start.') }}</p>
    </div>
    <div class="space-y-3">
        @foreach($faqs as $faq)
            <x-landing.faq-item :question="$faq['q']" :answer="$faq['a']" />
        @endforeach
    </div>
</section>

{{-- Final CTA --}}
<section class="px-4 pb-16 sm:px-6 lg:px-8">
    <div class="relative mx-auto max-w-5xl overflow-hidden rounded-3xl bg-gradient-to-l from-brand-700 to-brand-900 px-6 py-14 text-center text-white shadow-2xl" data-reveal>
        <div class="absolute -top-16 end-0 h-48 w-48 rounded-full bg-gold-500/20 blur-3xl"></div>
        <h2 class="text-3xl font-extrabold sm:text-4xl">{{ __('Ready to start your journey?') }}</h2>
        <p class="mx-auto mt-3 max-w-xl text-brand-100">{{ __('Create your account now and take the first step.') }}</p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('register') }}" class="rounded-xl bg-gold-500 px-6 py-3 font-semibold text-brand-950 shadow-lg transition hover:bg-gold-400">{{ __('Create Free Account') }}</a>
            <a href="{{ route('login') }}" class="rounded-xl border border-white/30 px-6 py-3 font-semibold text-white transition hover:bg-white/10">{{ __('Login') }}</a>
        </div>
    </div>
</section>
@endsection
