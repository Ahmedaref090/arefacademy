@extends('layouts.app')
@section('title', 'My Courses – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">My Courses</h1>

<div class="grid gap-4 md:grid-cols-2">
    @forelse($enrollments as $enrollment)
        @php($progress = $enrollment->course->progressFor(auth()->user()))
        <div class="card">
            <div class="mb-1 flex items-center justify-between">
                <a class="font-semibold hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $enrollment->course) }}">{{ $enrollment->course->title }}</a>
                <span class="text-xs text-gray-400">{{ $enrollment->course->lessons->count() }} lessons</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
            </div>
            <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $progress }}% complete</span>
                @php($firstLesson = $enrollment->course->lessons->first())
                @if($firstLesson)
                    <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('lessons.show', $firstLesson) }}">Continue →</a>
                @endif
            </div>
        </div>
    @empty
        @if($approvedMonths->isEmpty())
            <div class="card text-sm text-gray-500 dark:text-gray-400">
                No active enrollments. <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('courses.index') }}">Browse courses →</a>
            </div>
        @endif
    @endforelse
</div>

{{-- Per-month courses: appear here once at least one month is approved. --}}
@if($approvedMonths->isNotEmpty())
    <h2 class="mb-3 mt-8 font-semibold">Monthly Subscriptions</h2>
    <div class="grid gap-4 md:grid-cols-2">
        @foreach($approvedMonths->groupBy('course_id') as $months)
            @php($course = $months->first()->course)
            <div class="card">
                <div class="mb-1 flex items-center justify-between">
                    <a class="font-semibold hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $course) }}">{{ $course->title }}</a>
                    <span class="text-xs text-gray-400">{{ $months->count() }} {{ \Illuminate\Support\Str::plural('month', $months->count()) }}</span>
                </div>
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($months as $month)
                        <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ $month->name }}</span>
                    @endforeach
                </div>
                <div class="mt-2 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>Access limited to subscribed months</span>
                    @php($firstLesson = $months->first()->lessons->first())
                    @if($firstLesson)
                        <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('lessons.show', $firstLesson) }}">Continue →</a>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
