@extends('layouts.app')
@section('title', __('Edit :title – Aref Academy', ['title' => $lesson->title]))

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('admin.courses.edit', $lesson->course) }}">{{ $lesson->course->title }}</a>
    <span class="mx-1">/</span> {{ $lesson->title }}
</div>

<h1 class="mb-6 text-2xl font-bold">{{ __('Edit Lesson') }}</h1>

<form method="POST" action="{{ route('admin.lessons.update', $lesson) }}" enctype="multipart/form-data"
      x-data="r2Upload"
      data-url="{{ route('admin.videos.presigned-upload') }}"
      data-token="{{ csrf_token() }}"
      @submit="if (uploading) $event.preventDefault()"
      class="card mb-8 max-w-2xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.lessons._form')
    <button type="submit" class="btn w-full"
            :disabled="uploading"
            :class="{ 'opacity-50 cursor-not-allowed': uploading }">
        <template x-if="uploading">
            <span>{{ __('uploading_video') }}</span>
        </template>
        <template x-if="!uploading">
            <span>{{ __('Save Changes') }}</span>
        </template>
    </button>
</form>

{{-- Existing attachments --}}
@if($lesson->attachments->isNotEmpty())
    <div class="card mb-8 max-w-2xl">
        <h2 class="mb-3 font-semibold">{{ __('Attachments') }}</h2>
        <ul class="space-y-2 text-sm">
            @foreach($lesson->attachments as $attachment)
                <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                    <span>{{ $attachment->title }} <span class="text-xs text-gray-400">({{ $attachment->humanSize() }})</span></span>
                    <form method="POST" action="{{ route('admin.attachments.destroy', $attachment) }}" onsubmit="return confirm(@json(__('Delete this attachment?')));">
                        @csrf @method('DELETE')
                        <button class="btn-danger">{{ __('Delete') }}</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Quizzes --}}
<div class="card mb-8 max-w-2xl">
    <div class="mb-3 flex items-center justify-between">
        <h2 class="font-semibold">{{ __('Quizzes') }}</h2>
        <a class="btn" href="{{ route('admin.lessons.quizzes.create', $lesson) }}">{{ __('+ New Quiz') }}</a>
    </div>
    <ul class="space-y-2 text-sm">
        @forelse($lesson->quizzes as $quiz)
            <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                <span>{{ $quiz->title }} <span class="text-xs text-gray-400">({{ $quiz->questions->count() }} {{ __('questions') }} · {{ __('pass') }} {{ $quiz->pass_score }}%)</span></span>
                <div class="flex gap-2">
                    <a class="btn-secondary" href="{{ route('admin.quizzes.edit', $quiz) }}">{{ __('Edit') }}</a>
                    <form method="POST" action="{{ route('admin.quizzes.destroy', $quiz) }}" onsubmit="return confirm(@json(__('Delete this quiz?')));">
                        @csrf @method('DELETE')
                        <button class="btn-danger">{{ __('Delete') }}</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="text-gray-400">{{ __('No quizzes yet.') }}</li>
        @endforelse
    </ul>
</div>

{{-- Assignments --}}
<div class="card max-w-2xl">
    <h2 class="mb-3 font-semibold">{{ __('Assignments') }}</h2>
    <ul class="mb-4 space-y-2 text-sm">
        @forelse($lesson->assignments as $assignment)
            <li class="rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800" x-data="{ open: false }">
                <div class="flex items-center justify-between">
                    <span>{{ $assignment->title }} <span class="text-xs text-gray-400">({{ __('max') }} {{ $assignment->max_score }}@if($assignment->deadline) · {{ __('due') }} {{ $assignment->deadline->format('Y-m-d') }}@endif)</span></span>
                    <div class="flex gap-2">
                        <button type="button" class="btn-secondary" @click="open = !open">{{ __('Edit') }}</button>
                        <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" onsubmit="return confirm(@json(__('Delete this assignment?')));">
                            @csrf @method('DELETE')
                            <button class="btn-danger">{{ __('Delete') }}</button>
                        </form>
                    </div>
                </div>
                <form x-show="open" x-cloak method="POST" action="{{ route('admin.assignments.update', $assignment) }}" class="mt-3 space-y-3 border-t border-gray-200 pt-3 dark:border-gray-800">
                    @csrf @method('PUT')
                    @php $aT = $assignment->rawTranslations('title'); $aD = $assignment->rawTranslations('description'); @endphp
                    <div class="grid grid-cols-2 gap-3">
                        <input class="input" name="title_ar" value="{{ $aT['ar'] ?? '' }}" placeholder="{{ __('Assignment title') }} (AR)" required>
                        <input class="input" name="title_en" value="{{ $aT['en'] ?? '' }}" placeholder="{{ __('Assignment title') }} (EN)">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <textarea class="input" name="description_ar" rows="2" placeholder="{{ __('Instructions (optional)') }} (AR)">{{ $aD['ar'] ?? '' }}</textarea>
                        <textarea class="input" name="description_en" rows="2" placeholder="{{ __('Instructions (optional)') }} (EN)">{{ $aD['en'] ?? '' }}</textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <input class="input" name="max_score" type="number" min="1" value="{{ $assignment->max_score }}" required>
                        <input class="input" name="deadline" type="date" value="{{ $assignment->deadline?->format('Y-m-d') }}">
                    </div>
                    <button class="btn">{{ __('Save') }}</button>
                </form>
            </li>
        @empty
            <li class="text-gray-400">{{ __('No assignments yet.') }}</li>
        @endforelse
    </ul>

    <form method="POST" action="{{ route('admin.lessons.assignments.store', $lesson) }}" class="space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
        @csrf
        <h3 class="text-sm font-semibold">{{ __('Add Assignment') }}</h3>
        <div class="grid grid-cols-2 gap-3">
            <input class="input" name="title_ar" placeholder="{{ __('Assignment title') }} (AR)" required>
            <input class="input" name="title_en" placeholder="{{ __('Assignment title') }} (EN)">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <textarea class="input" name="description_ar" rows="2" placeholder="{{ __('Instructions (optional)') }} (AR)"></textarea>
            <textarea class="input" name="description_en" rows="2" placeholder="{{ __('Instructions (optional)') }} (EN)"></textarea>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <input class="input" name="max_score" type="number" min="1" value="100" placeholder="{{ __('Max score') }}" required>
            <input class="input" name="deadline" type="date">
        </div>
        <button class="btn">{{ __('Add Assignment') }}</button>
    </form>
</div>
@endsection
