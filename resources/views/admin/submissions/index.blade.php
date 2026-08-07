@extends('layouts.app')
@section('title', __('Submissions – Aref Academy'))

@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-2xl font-bold">{{ __('Assignment Submissions') }}</h1>
    <div class="flex gap-2 text-sm">
        <a class="btn-secondary" href="{{ route('admin.submissions.index') }}">{{ __('All') }}</a>
        <a class="btn-secondary" href="{{ route('admin.submissions.index', ['status' => 'pending']) }}">{{ __('Pending') }}</a>
        <a class="btn-secondary" href="{{ route('admin.submissions.index', ['status' => 'graded']) }}">{{ __('Graded') }}</a>
    </div>
</div>

<div class="space-y-4">
    @forelse($submissions as $submission)
        <div class="card">
            <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <span class="font-semibold">{{ $submission->user->name }}</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">→ {{ $submission->assignment->title }} ({{ $submission->assignment->lesson->course->title }})</span>
                </div>
                <span class="text-xs text-gray-400">{{ $submission->created_at->format('Y-m-d H:i') }}</span>
            </div>

            <div class="mb-3 space-y-1 text-sm">
                @if($submission->file_path)
                    <div>📎 <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('admin.files.show', $submission->file_path) }}" target="_blank">{{ __('Download submitted file') }}</a></div>
                @endif
                @if($submission->code)
                    <details>
                        <summary class="cursor-pointer text-indigo-600 dark:text-indigo-400">{{ __('View code') }}</summary>
                        <pre class="mt-2 overflow-x-auto rounded-lg bg-gray-900 p-3 font-mono text-xs text-gray-100">{{ $submission->code }}</pre>
                    </details>
                @endif
            </div>

            @if($submission->isGraded())
                <div class="text-sm">
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">{{ __('Graded') }}: {{ $submission->score }}/{{ $submission->assignment->max_score }}</span>
                    @if($submission->feedback)<span class="ml-2 text-gray-500 dark:text-gray-400">{{ $submission->feedback }}</span>@endif
                </div>
            @else
                <form method="POST" action="{{ route('admin.submissions.grade', $submission) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div>
                        <label class="label">{{ __('Score (max :max)', ['max' => $submission->assignment->max_score]) }}</label>
                        <input class="input w-28" name="score" type="number" min="0" max="{{ $submission->assignment->max_score }}" required>
                    </div>
                    <div class="min-w-52 flex-1">
                        <label class="label">{{ __('Feedback (optional)') }}</label>
                        <input class="input" name="feedback" placeholder="{{ __('Great work! / Needs improvement…') }}">
                    </div>
                    <button class="btn">{{ __('Grade') }}</button>
                </form>
            @endif
        </div>
    @empty
        <div class="card text-sm text-gray-400">{{ __('No submissions found.') }}</div>
    @endforelse
</div>

<div class="mt-6">{{ $submissions->links() }}</div>
@endsection
