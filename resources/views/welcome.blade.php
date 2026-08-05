@extends('layouts.guest')
@section('title', __('Aref in Programming') . ' – ' . __('Learn programming the right way.'))

@section('content')
{{-- Hero --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-burgundy-50 via-white to-gold-50 dark:from-slate-900 dark:via-slate-950 dark:to-burgundy-950"></div>
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:py-24">
        <div>
            <h1 class="text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                {{ __('Aref in Programming') }}
            </h1>
            <p class="mt-3 bg-gradient-to-r from-burgundy-600 to-gold-500 bg-clip-text text-2xl font-bold text-transparent sm:text-3xl">
                {{ __('Learn programming the right way.') }}
            </p>
            <p class="mt-4 max-w-lg text-lg text-slate-600 dark:text-slate-300">
                {{ __('Structured courses, real projects, quizzes and assignments — everything you need to go from zero to professional.') }}
            </p>
            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ route('register') }}" class="rounded-xl bg-burgundy-600 px-6 py-3 font-semibold text-white shadow-lg shadow-burgundy-600/30 hover:bg-burgundy-700">{{ __('Get Started') }}</a>
                <a href="#courses" class="rounded-xl border border-slate-300 px-6 py-3 font-semibold hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">{{ __('Browse Courses') }}</a>
            </div>
        </div>
        <div class="flex justify-center lg:justify-end">
            {{-- Replace /images/instructor.png with your own photo --}}
            <img src="/images/instructor.png" alt="{{ __('Instructor') }}"
                class="h-80 w-80 rounded-3xl border-4 border-white object-cover shadow-2xl dark:border-slate-800 lg:h-96 lg:w-96"
                onerror="this.src='https://placehold.co/600x600/7b2d43/ffffff?text=Instructor'">
        </div>
    </div>
</section>

{{-- Courses --}}
<section id="courses" class="mx-auto max-w-7xl px-4 py-16 sm:px-6">
    <h2 class="mb-2 text-3xl font-extrabold">{{ __('Available Courses') }}</h2>
    <p class="mb-8 text-slate-500 dark:text-slate-400">{{ __('Pick a course and start learning today.') }}</p>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($courses as $course)
            <a href="{{ route('login') }}" class="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                <div class="aspect-video w-full overflow-hidden bg-slate-100 dark:bg-slate-800">
                    @if($course->thumbnail)
                        <img src="{{ Storage::disk('public')->url($course->thumbnail) }}" alt="{{ $course->title }}" class="h-full w-full object-cover transition group-hover:scale-105">
                    @else
                        <div class="flex h-full items-center justify-center font-mono text-4xl text-burgundy-300">&lt;/&gt;</div>
                    @endif
                </div>
                <div class="p-5">
                    <h3 class="font-bold">{{ $course->title }}</h3>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
                    <div class="mt-4 flex items-center justify-between">
                        <span class="font-mono font-bold text-burgundy-600 dark:text-gold-400">
                            {{ (float) $course->price > 0 ? number_format($course->price, 2) . ' ' . __('EGP') : __('Free') }}
                        </span>
                        <span class="rounded-lg bg-burgundy-600 px-3 py-1.5 text-xs font-semibold text-white group-hover:bg-burgundy-700">{{ __('Subscribe') }}</span>
                    </div>
                </div>
            </a>
        @empty
            <p class="col-span-full text-slate-400">{{ __('No courses available yet.') }}</p>
        @endforelse
    </div>
</section>

{{-- How it works --}}
<section class="border-y border-slate-200 bg-white py-16 dark:border-slate-800 dark:bg-slate-900">
    <div class="mx-auto max-w-7xl px-4 sm:px-6">
        <h2 class="mb-10 text-center text-3xl font-extrabold">{{ __('How do I start on the platform?') }}</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach([
                ['icon' => '👤', 'title' => __('Create Account'), 'desc' => __('Register with your phone number in seconds.')],
                ['icon' => '📚', 'title' => __('Select Course'), 'desc' => __('Choose the course that fits your level.')],
                ['icon' => '▶️', 'title' => __('Watch & Learn'), 'desc' => __('Follow the lessons at your own pace.')],
                ['icon' => '🧠', 'title' => __('Test Yourself'), 'desc' => __('Quizzes and assignments to prove your skills.')],
            ] as $i => $step)
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center dark:border-slate-800 dark:bg-slate-950">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-burgundy-600/10 text-3xl">{{ $step['icon'] }}</div>
                    <div class="mb-1 font-mono text-xs font-bold text-gold-500">{{ __('Step') }} {{ $i + 1 }}</div>
                    <h3 class="font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
