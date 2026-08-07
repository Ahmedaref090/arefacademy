@extends('layouts.app')
@section('title', __('Edit :title – Aref Academy', ['title' => $course->title]))

@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('Edit Course') }}</h1>

<form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="card mb-8 max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.courses._form')
    <button class="btn">{{ __('Save Changes') }}</button>
</form>

@if($course->isPerMonth())
    <div class="card mb-8">
        <h2 class="mb-4 font-semibold">{{ __('Months (:count)', ['count' => $course->months->count()]) }}</h2>

        <form method="POST" action="{{ route('admin.courses.months.store', $course) }}" class="mb-4 flex items-end gap-3">
            @csrf
            <div class="flex-1">
                <label class="label" for="month_name">{{ __('Month name') }}</label>
                <div class="flex gap-2">
                    <input class="input" id="month_name" name="name_ar" placeholder="{{ __('Month name') }} (AR)" dir="rtl" required>
                    <input class="input" name="name_en" placeholder="{{ __('Month name') }} (EN)">
                </div>
            </div>
            <div class="w-28">
                <label class="label" for="month_sort_order">{{ __('Order') }}</label>
                <input class="input" id="month_sort_order" name="sort_order" type="number" min="0" value="{{ $course->months->count() + 1 }}">
            </div>
            <button class="btn">{{ __('+ Add Month') }}</button>
        </form>

        <ul class="divide-y divide-gray-200 dark:divide-gray-800">
            @forelse($course->months as $month)
                <li class="flex items-center justify-between gap-3 py-3">
                    <form method="POST" action="{{ route('admin.months.update', $month) }}" class="flex flex-1 items-center gap-3">
                        @csrf @method('PUT')
                        @php $mN = $month->rawTranslations('name'); @endphp
                        <input class="input" name="name_ar" value="{{ $mN['ar'] ?? '' }}" required>
                        <input class="input" name="name_en" value="{{ $mN['en'] ?? '' }}">
                        <input class="input w-24" name="sort_order" type="number" min="0" value="{{ $month->sort_order }}">
                        <button class="btn-secondary">{{ __('Save') }}</button>
                    </form>
                    <form method="POST" action="{{ route('admin.months.destroy', $month) }}" onsubmit="return confirm(@json(__('Delete this month? Its lessons will become unassigned.')));">
                        @csrf @method('DELETE')
                        <button class="btn-danger">{{ __('Delete') }}</button>
                    </form>
                </li>
            @empty
                <li class="py-3 text-sm text-gray-400">{{ __('No months yet. Add the months this course is divided into (e.g. August, September).') }}</li>
            @endforelse
        </ul>
    </div>
@endif

<div class="card">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="font-semibold">{{ __('Lessons (:count)', ['count' => $course->lessons->count()]) }}</h2>
        <a class="btn" href="{{ route('admin.courses.lessons.create', $course) }}">{{ __('+ Add Lesson') }}</a>
    </div>
    <ul class="divide-y divide-gray-200 dark:divide-gray-800">
        @forelse($course->lessons as $i => $lesson)
            <li class="flex items-center justify-between gap-3 py-3">
                <div class="flex items-center gap-3">
                    <span class="font-mono text-xs text-gray-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="font-medium">{{ $lesson->title }}</span>
                    @if($lesson->is_free)<span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">{{ __('Free') }}</span>@endif
                    <span class="text-xs text-gray-400">{{ __(':quizzes quiz · :assignments assignment', ['quizzes' => $lesson->quizzes->count(), 'assignments' => $lesson->assignments->count()]) }}</span>
                </div>
                <div class="flex gap-2">
                    <a class="btn-secondary" href="{{ route('admin.lessons.quizzes.create', $lesson) }}">{{ __('+ Quiz') }}</a>
                    <a class="btn-secondary" href="{{ route('admin.lessons.edit', $lesson) }}">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('admin.lessons.destroy', $lesson) }}" onsubmit="return confirm(@json(__('Delete this lesson?')));">
                        @csrf @method('DELETE')
                        <button class="btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="py-3 text-sm text-gray-400">{{ __('No lessons yet.') }}</li>
        @endforelse
    </ul>
</div>
@endsection
