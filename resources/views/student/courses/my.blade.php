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
        <div class="card text-sm text-gray-500 dark:text-gray-400">
            No active enrollments. <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('courses.index') }}">Browse courses →</a>
        </div>
    @endforelse
</div>
@endsection
