@extends('layouts.app')
@section('title', 'My Courses – Aref Academy')

@section('content')
@php
    $user = auth()->user();
    $progressMap = $enrollments->mapWithKeys(fn ($e) => [$e->id => $e->course->progressFor($user)]);
    $avgProgress = $progressMap->isNotEmpty() ? (int) round($progressMap->avg()) : 0;
    $monthlyCourses = $approvedMonths->groupBy('course_id');
    $totalCourses = $enrollments->count() + $monthlyCourses->count();
@endphp

{{-- Hero header with quick stats --}}
<div class="relative mb-8 overflow-hidden rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 p-6 text-white shadow-lg sm:p-8">
    <div class="pointer-events-none absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-12 -right-8 h-48 w-48 rounded-full bg-purple-300/20 blur-2xl"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold sm:text-3xl">🎓 My Learning Journey</h1>
            <p class="mt-1 text-sm text-indigo-100">Track your progress and pick up right where you left off.</p>
        </div>
        <div class="flex gap-3 text-center">
            <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                <div class="text-xl font-bold">{{ $totalCourses }}</div>
                <div class="text-xs text-indigo-100">{{ \Illuminate\Support\Str::plural('Course', $totalCourses) }}</div>
            </div>
            <div class="rounded-xl bg-white/15 px-4 py-2 backdrop-blur">
                <div class="text-xl font-bold">{{ $avgProgress }}%</div>
                <div class="text-xs text-indigo-100">Avg. progress</div>
            </div>
        </div>
    </div>
</div>

{{-- Full-course enrollments --}}
@if($enrollments->isNotEmpty())
    <h2 class="mb-4 flex items-center gap-2 font-semibold">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-sm dark:bg-indigo-500/10">📖</span>
        Full Courses
    </h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($enrollments as $enrollment)
            @php
                $course = $enrollment->course;
                $progress = $progressMap[$enrollment->id];
                $firstLesson = $course->lessons->first();
            @endphp
            <div class="group card overflow-hidden p-0 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-indigo-500/10">
                <a href="{{ route('courses.show', $course) }}" class="relative block h-36 overflow-hidden">
                    @if($course->thumbnailUrl())
                        <img src="{{ $course->thumbnailUrl() }}" alt="{{ $course->title }}"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-indigo-500 to-purple-600 text-4xl font-bold text-white/90">
                            {{ mb_substr($course->title, 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/50 to-transparent"></span>
                    <span class="absolute bottom-2 left-3 text-xs font-medium text-white/90">{{ $course->lessons->count() }} lessons</span>
                    @if($progress === 100)
                        <span class="absolute right-3 top-3 rounded-full bg-emerald-500 px-2.5 py-1 text-xs font-semibold text-white shadow">✓ Completed</span>
                    @endif
                </a>
                <div class="p-4">
                    <a class="font-semibold transition-colors hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $course) }}">{{ $course->title }}</a>
                    <div class="mt-3 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>Progress</span>
                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ $progress }}%</span>
                    </div>
                    <div class="mt-1 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 transition-all duration-700" style="width: {{ $progress }}%"></div>
                    </div>
                    @if($firstLesson)
                        <a href="{{ route('lessons.show', $firstLesson) }}" class="btn mt-4 w-full transition-transform duration-200 group-hover:scale-[1.02]">
                            {{ $progress > 0 && $progress < 100 ? 'Continue Learning →' : ($progress === 100 ? 'Review Course →' : 'Start Learning →') }}
                        </a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Per-month courses: appear here once at least one month is approved. --}}
@if($monthlyCourses->isNotEmpty())
    <h2 class="mb-4 mt-10 flex items-center gap-2 font-semibold">
        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-sm dark:bg-emerald-500/10">🗓️</span>
        Monthly Subscriptions
    </h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($monthlyCourses as $months)
            @php
                $course = $months->first()->course;
                $firstLesson = $months->first()->lessons->first();
            @endphp
            <div class="group card overflow-hidden p-0 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <a href="{{ route('courses.show', $course) }}" class="relative block h-36 overflow-hidden">
                    @if($course->thumbnailUrl())
                        <img src="{{ $course->thumbnailUrl() }}" alt="{{ $course->title }}"
                            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                    @else
                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-emerald-500 to-teal-600 text-4xl font-bold text-white/90">
                            {{ mb_substr($course->title, 0, 1) }}
                        </div>
                    @endif
                    <span class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/50 to-transparent"></span>
                    <span class="absolute bottom-2 left-3 text-xs font-medium text-white/90">
                        {{ $months->count() }} {{ \Illuminate\Support\Str::plural('month', $months->count()) }} subscribed
                    </span>
                </a>
                <div class="p-4">
                    <a class="font-semibold transition-colors hover:text-emerald-600 dark:hover:text-emerald-400" href="{{ route('courses.show', $course) }}">{{ $course->title }}</a>
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($months as $month)
                            <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">✓ {{ $month->name }}</span>
                        @endforeach
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Access limited to your subscribed months.</p>
                    @if($firstLesson)
                        <a href="{{ route('lessons.show', $firstLesson) }}" class="btn mt-4 w-full transition-transform duration-200 group-hover:scale-[1.02]">Continue Learning →</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif

{{-- Empty state --}}
@if($enrollments->isEmpty() && $approvedMonths->isEmpty())
    <div class="card flex flex-col items-center py-16 text-center">
        <div class="mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-4xl dark:bg-indigo-500/10">📚</div>
        <h2 class="text-lg font-semibold">No courses yet</h2>
        <p class="mt-1 max-w-sm text-sm text-gray-500 dark:text-gray-400">
            Once you enroll in a course or subscribe to a month, it will show up here with your progress.
        </p>
        <a href="{{ route('courses.index') }}" class="btn mt-6">Browse Courses →</a>
    </div>
@endif
@endsection
