@extends('layouts.app')
@section('title', 'Home – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Welcome back, {{ auth()->user()->name }} 👋</h1>

{{-- Stat cards --}}
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    <div class="card"><div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['courses'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">My Courses</div></div>
    <div class="card"><div class="text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed_lessons'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Lessons Completed</div></div>
    <div class="card"><div class="text-3xl font-bold text-amber-500">{{ $stats['avg_quiz_score'] }}%</div><div class="text-sm text-gray-500 dark:text-gray-400">Avg Quiz Score</div></div>
    <div class="card"><div class="text-3xl font-bold text-sky-500">{{ $stats['watch_minutes'] }}</div><div class="text-sm text-gray-500 dark:text-gray-400">Minutes Watched</div></div>
</div>

{{-- Activity chart (last 14 days) --}}
<div class="card mb-8">
    <h2 class="mb-4 font-semibold">Learning Activity (last 14 days)</h2>
    @php($max = max(1, $chart->max()))
    <div class="flex h-32 items-end gap-1">
        @foreach($chart as $day => $count)
            <div class="flex-1 rounded-t bg-indigo-500/80" style="height: {{ max(4, $count / $max * 100) }}%" title="{{ $day }}: {{ $count }} lesson(s)"></div>
        @endforeach
    </div>
    <div class="mt-2 flex justify-between text-xs text-gray-400">
        <span>{{ $chart->keys()->first() }}</span><span>{{ $chart->keys()->last() }}</span>
    </div>
</div>

{{-- Continue learning --}}
<h2 class="mb-4 font-semibold">Continue Learning</h2>
<div class="grid gap-4 md:grid-cols-2">
    @forelse($enrollments as $enrollment)
        @php($progress = $enrollment->course->progressFor(auth()->user()))
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
@endsection
