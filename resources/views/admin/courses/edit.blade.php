@extends('layouts.app')
@section('title', 'Edit ' . $course->title . ' – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Edit Course</h1>

<form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="card mb-8 max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.courses._form')
    <button class="btn">Save Changes</button>
</form>

@if($course->isPerMonth())
    <div class="card mb-8">
        <h2 class="mb-4 font-semibold">Months ({{ $course->months->count() }})</h2>

        <form method="POST" action="{{ route('admin.courses.months.store', $course) }}" class="mb-4 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="label" for="month_name">Month name</label>
                <input class="input" id="month_name" name="name" placeholder="e.g. August" required>
            </div>
            <div class="w-28">
                <label class="label" for="month_sort_order">Order</label>
                <input class="input" id="month_sort_order" name="sort_order" type="number" min="0" value="{{ $course->months->count() + 1 }}">
            </div>
            <button class="btn">+ Add Month</button>
        </form>

        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($course->months as $month)
                <li class="flex items-center justify-between gap-3 py-3">
                    <form method="POST" action="{{ route('admin.months.update', $month) }}" class="flex flex-1 items-center gap-3">
                        @csrf @method('PUT')
                        <input class="input" name="name" value="{{ $month->name }}" required>
                        <input class="input w-24" name="sort_order" type="number" min="0" value="{{ $month->sort_order }}">
                        <button class="btn-secondary">Save</button>
                    </form>
                    <form method="POST" action="{{ route('admin.months.destroy', $month) }}" onsubmit="return confirm('Delete this month? Its lessons will become unassigned.')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Delete</button>
                    </form>
                </li>
            @empty
                <li class="py-3 text-sm text-gray-400">No months yet. Add the months this course is divided into (e.g. August, September).</li>
            @endforelse
        </ul>
    </div>
@endif

<div class="card">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold">Lessons ({{ $course->lessons->count() }})</h2>
        <a class="btn" href="{{ route('admin.courses.lessons.create', $course) }}">+ Add Lesson</a>
    </div>
    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
        @forelse($course->lessons as $i => $lesson)
            <li class="flex items-center justify-between gap-3 py-3">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-medium">{{ $lesson->title }}</span>
                    @if($lesson->is_free)<span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">Free</span>@endif
                    <span class="text-xs text-gray-400">{{ $lesson->quizzes->count() }} quiz · {{ $lesson->assignments->count() }} assignment</span>
                </div>
                <div class="flex gap-2">
                    <a class="btn-secondary" href="{{ route('admin.lessons.quizzes.create', $lesson) }}">+ Quiz</a>
                    <a class="btn-secondary" href="{{ route('admin.lessons.edit', $lesson) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm('Delete this lesson?')">
                        @csrf @method('DELETE')
                        <button class="btn-danger">Delete</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="py-3 text-sm text-gray-400">No lessons yet.</li>
        @endforelse
    </ul>
</div>
@endsection
