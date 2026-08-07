@extends('layouts.landing')
@section('title', __('Aref in Programming') . ' – ' . __('Learn programming the right way.'))

@section('content')
@php
    // Group courses by grade level (enum-cast, plain string, or none).
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
    <div class="blob -top-24 start-0 h-80 w-80 bg-brand-400/40"></div>
    <div class="blob -bottom-24 end-0 h-80 w-80 bg-gold-400/40" style="animation-delay:-6s"></div>
    <div class="blob top-1/2 start-1/2 h-72 w-72 bg-fuchsia-400/30" style="animation-delay:-11s"></div>

    <div class="mx-auto grid max-w-7xl items-center gap-14 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:gap-16 lg:px-8 lg:py-28">
        <div data-reveal>
            <span class="eyebrow"><x-icon name="sparkles" class="h-4 w-4" :stroke="2"/> {{ __('Programming learning platform') }}</span>
            <h1 class="mt-5 text-4xl font-extrabold leading-[1.08] tracking-tight sm:text-5xl lg:text-6xl">
                <span class="text-gradient-animated">{{ __('Aref in Programming') }}</span>
            </h1>
            <p class="mt-4 text-2xl font-bold text-slate-700 dark:text-slate-200 sm:text-3xl">
                {{ __('Learn programming the right way.') }}
            </p>
            <p class="mt-4 max-w-lg text-pretty text-lg text-slate-600 dark:text-slate-300">
                {{ __('Structured courses, real projects, quizzes and assignments — everything you need to go from zero to professional.') }}
            </p>
            <div class="mt-9 flex flex-wrap gap-4">
                <a href="{{ route('register') }}" class="btn !px-8 !py-3.5 !text-base"><x-icon name="rocket" class="h-5 w-5" :stroke="1.8"/> {{ __('Get Started') }}</a>
                <a href="#courses" class="btn-secondary !px-8 !py-3.5 !text-base"><x-icon name="book" class="h-5 w-5" :stroke="1.8"/> {{ __('Browse Courses') }}</a>
            </div>
            <ul class="mt-9 flex flex-wrap gap-x-7 gap-y-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                @foreach([
                    ['i' => 'check', 't' => __('Arabic explanations')],
                    ['i' => 'code', 't' => __('Hands-on projects')],
                    ['i' => 'trophy', 't' => __('Quizzes & assignments')],
                ] as $perk)
                    <li class="flex items-center gap-2"><span class="grid h-6 w-6 place-items-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400"><x-icon name="{{ $perk['i'] }}" class="h-4 w-4" :stroke="2.2"/></span> {{ $perk['t'] }}</li>
                @endforeach
            </ul>
        </div>

        <div class="relative pb-10" data-reveal data-reveal-delay="1">
            <div class="animate-float"><x-landing.code-window /></div>
            {{-- Floating instructor card --}}
            <div class="absolute -bottom-5 right-4 flex w-60 items-center gap-3 rounded-2xl border border-slate-200/80 bg-white/90 p-3 shadow-2xl backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 sm:w-64">
                <img src="/images/instructor.png" alt="{{ __('Instructor') }}"
                     class="h-12 w-12 shrink-0 rounded-xl object-cover ring-2 ring-brand-500/30"
                     onerror="this.src='https://placehold.co/96x96/6d38f6/ffffff?text=A'">
                <div class="min-w-0">
                    <div class="truncate text-sm font-extrabold">{{ __('Your instructor') }}</div>
                    <div class="truncate text-xs text-slate-500 dark:text-slate-400">{{ __('With you step by step') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Courses (with grade-level tabs) --}}
<section id="courses" class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8" x-data="{ tab: 'all' }">
    <div class="mb-10 text-center" data-reveal>
        <span class="eyebrow"><x-icon name="book" class="h-4 w-4" :stroke="2"/> {{ __('Our catalog') }}</span>
        <h2 class="mt-4 text-4xl font-extrabold sm:text-5xl">{{ __('Available Courses') }}</h2>
        <p class="mt-3 text-lg text-slate-500 dark:text-slate-400">{{ __('Pick a course and start learning today.') }}</p>
    </div>

    @if($showTabs)
        <div class="mb-9 flex flex-wrap justify-center gap-2.5" data-reveal>
            <button type="button" @click="tab = 'all'"
                    :class="tab === 'all' ? 'border-transparent bg-gradient-to-br from-brand-600 to-fuchsia-600 text-white shadow-lg shadow-brand-500/30' : 'border-slate-300 bg-white text-slate-600 hover:border-brand-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                    class="rounded-full border px-5 py-2 text-sm font-bold transition-all duration-300 hover:-translate-y-0.5">{{ __('All') }}</button>
            @foreach($grouped as $key => $group)
                @if($key !== 'general')
                    <button type="button" @click="tab = '{{ $key }}'"
                            :class="tab === '{{ $key }}' ? 'border-transparent bg-gradient-to-br from-brand-600 to-fuchsia-600 text-white shadow-lg shadow-brand-500/30' : 'border-slate-300 bg-white text-slate-600 hover:border-brand-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300'"
                            class="rounded-full border px-5 py-2 text-sm font-bold transition-all duration-300 hover:-translate-y-0.5">{{ $gradeLabel($group->first()) }}</button>
                @endif
            @endforeach
        </div>
    @endif

    <div class="grid gap-7 sm:grid-cols-2 lg:grid-cols-3" data-reveal>
        @forelse($courses as $course)
            <x-landing.course-card :course="$course" :grade-key="$gradeKey($course)" />
        @empty
            <p class="col-span-full text-center text-slate-400">{{ __('No courses available yet.') }}</p>
        @endforelse
    </div>
</section>

{{-- How it works --}}
<section id="how" class="border-y border-slate-200/70 bg-white/70 py-20 backdrop-blur dark:border-slate-800 dark:bg-slate-900/60">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-14 text-center" data-reveal>
            <span class="eyebrow"><x-icon name="layers" class="h-4 w-4" :stroke="2"/> {{ __('Simple') }}</span>
            <h2 class="mt-4 text-4xl font-extrabold sm:text-5xl">{{ __('How do I start on the platform?') }}</h2>
            <p class="mt-3 text-lg text-slate-500 dark:text-slate-400">{{ __('Four simple steps and you are in.') }}</p>
        </div>
        <div class="relative grid gap-7 sm:grid-cols-2 lg:grid-cols-4">
            <div class="absolute inset-x-16 top-7 hidden border-t-2 border-dashed border-brand-200 dark:border-brand-800 lg:block"></div>
            @foreach([
                ['icon' => 'user-plus', 'title' => __('Create Account'), 'desc' => __('Register with your phone number in seconds.')],
                ['icon' => 'book', 'title' => __('Select Course'), 'desc' => __('Choose the course that fits your level.')],
                ['icon' => 'play', 'title' => __('Watch & Learn'), 'desc' => __('Follow the lessons at your own pace.')],
                ['icon' => 'brain', 'title' => __('Test Yourself'), 'desc' => __('Quizzes and assignments to prove your skills.')],
            ] as $i => $step)
                <div class="card-hover relative rounded-2xl border border-slate-200/80 bg-white p-7 text-center shadow-sm dark:border-slate-800 dark:bg-slate-950/60" data-reveal data-reveal-delay="{{ $i }}">
                    <div class="relative mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-fuchsia-600 text-white shadow-lg shadow-brand-500/30 animate-float" style="animation-delay: {{ $i * 0.8 }}s">
                        <x-icon name="{{ $step['icon'] }}" class="h-7 w-7" :stroke="1.8"/>
                    </div>
                    <div class="mb-1.5 inline-flex rounded-full bg-gold-100 px-3 py-0.5 font-mono text-xs font-bold text-gold-700 dark:bg-gold-500/10 dark:text-gold-400">{{ __('Step') }} {{ $i + 1 }}</div>
                    <h3 class="text-lg font-extrabold">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- FAQ --}}
<section id="faq" class="mx-auto max-w-3xl px-4 py-20 sm:px-6 lg:px-8">
    <div class="mb-12 text-center" data-reveal>
        <span class="eyebrow"><x-icon name="chat" class="h-4 w-4" :stroke="2"/> {{ __('Help') }}</span>
        <h2 class="mt-4 text-4xl font-extrabold sm:text-5xl">{{ __('Frequently Asked Questions') }}</h2>
        <p class="mt-3 text-lg text-slate-500 dark:text-slate-400">{{ __('Everything you need to know before you start.') }}</p>
    </div>
    <div class="space-y-4">
        @foreach($faqs as $faq)
            <x-landing.faq-item :question="$faq['q']" :answer="$faq['a']" />
        @endforeach
    </div>
</section>

{{-- Final CTA --}}
<section class="px-4 pb-20 sm:px-6 lg:px-8">
    <div class="relative mx-auto max-w-5xl overflow-hidden rounded-[2rem] px-6 py-16 text-center text-white sm:py-20" data-reveal>
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-700 via-brand-600 to-fuchsia-700"></div>
        <div class="blob -top-20 end-10 h-64 w-64 bg-gold-400/40"></div>
        <div class="blob -bottom-24 start-6 h-64 w-64 bg-fuchsia-300/40" style="animation-delay:-7s"></div>
        <h2 class="text-4xl font-extrabold sm:text-5xl">{{ __('Ready to start your journey?') }}</h2>
        <p class="mx-auto mt-4 max-w-xl text-lg text-brand-100">{{ __('Create your account now and take the first step.') }}</p>
        <div class="mt-9 flex flex-wrap justify-center gap-4">
            <a href="{{ route('register') }}" class="btn-gold !px-8 !py-3.5 !text-base"><x-icon name="sparkles" class="h-5 w-5" :stroke="2"/> {{ __('Create Free Account') }}</a>
            <a href="{{ route('login') }}" class="rounded-xl border-2 border-white/40 px-8 py-3.5 text-base font-bold text-white transition-all duration-300 hover:-translate-y-0.5 hover:bg-white/10 hover:shadow-xl">{{ __('Login') }}</a>
        </div>
    </div>
</section>

{{-- Contact Us (homepage only) --}}
<section id="contact" class="border-t border-slate-200/70 bg-white/70 py-20 backdrop-blur dark:border-slate-800 dark:bg-slate-900/60">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center" data-reveal>
            <span class="eyebrow"><x-icon name="phone" class="h-4 w-4" :stroke="2"/> {{ __('Contact') }}</span>
            <h2 class="mt-4 text-4xl font-extrabold sm:text-5xl">{{ __('Have a question?') }}</h2>
            <p class="mt-3 text-lg text-slate-500 dark:text-slate-400">{{ __('Reach out anytime.') }}</p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2" data-reveal>
            <a href="tel:+201068014651"
               class="card-hover group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/60">
                <span class="icon-tile-soft !h-14 !w-14 !rounded-2xl"><x-icon name="phone" class="h-6 w-6" :stroke="1.8"/></span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('Phone') }}</span>
                    <span class="block truncate font-mono text-lg font-bold text-slate-900 dark:text-white" dir="ltr">01068014651</span>
                </span>
            </a>

            <a href="mailto:ahmedaref009988@gmail.com"
               class="card-hover group flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/60">
                <span class="icon-tile-soft !h-14 !w-14 !rounded-2xl"><x-icon name="mail" class="h-6 w-6" :stroke="1.8"/></span>
                <span class="min-w-0">
                    <span class="block text-sm font-semibold text-slate-500 dark:text-slate-400">{{ __('Email') }}</span>
                    <span class="block truncate font-mono text-sm font-bold text-slate-900 dark:text-white sm:text-base" dir="ltr">ahmedaref009988@gmail.com</span>
                </span>
            </a>
        </div>
    </div>
</section>

{{-- Floating WhatsApp button (homepage only) --}}
<a href="https://wa.me/201068014651"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="{{ __('Contact') }}"
   class="fixed bottom-6 right-6 z-50 flex items-center gap-2 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 px-5 py-3 text-sm font-bold text-white shadow-xl shadow-emerald-500/40 transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-emerald-500/50">
    <svg class="h-6 w-6 shrink-0" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
    </svg>
    <span>{{ __('Contact') }}</span>
</a>
@endsection