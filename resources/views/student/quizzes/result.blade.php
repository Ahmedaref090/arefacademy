@extends('layouts.app')
@section('title', 'Quiz Result – Aref Academy')

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $quiz->lesson) }}">{{ $quiz->lesson->title }}</a>
    <span class="mx-1">/</span> {{ $quiz->title }} <span class="mx-1">/</span> Result
</div>

{{-- Score summary --}}
<div class="card mb-6 flex flex-wrap items-center justify-between gap-4 {{ $attempt->passed ? 'border-green-500' : 'border-red-500' }}">
    <div>
        <div class="text-3xl font-bold {{ $attempt->passed ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
            {{ $attempt->score }}/{{ $attempt->total_questions }} <span class="text-lg">({{ $attempt->percentage() }}%)</span>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            {{ $attempt->passed ? 'Passed! 🎉 Great work.' : 'Not passed — you need ' . $quiz->pass_score . '%.' }}
            <span class="text-xs text-gray-400">· {{ $attempt->completed_at?->format('Y-m-d H:i') }}</span>
        </div>
    </div>
    <div class="flex gap-2">
        @if($attemptsLeft === null || $attemptsLeft > 0)
            <a class="btn-secondary" href="{{ route('quizzes.show', $quiz) }}">Retake Quiz</a>
        @endif
        <a class="btn" href="{{ route('lessons.show', $quiz->lesson) }}">Back to Lesson</a>
    </div>
</div>

{{-- Per-question review (rows pre-computed in the controller) --}}
<div class="space-y-4">
    @foreach($review as $i => $item)
        <div class="card">
            <p class="mb-3 font-medium">
                {{ $i + 1 }}. {{ $item['question']->question_text }}
                @if($item['is_correct'])
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">Correct</span>
                @elseif($item['chosen'] === null)
                    <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Unanswered</span>
                @else
                    <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">Wrong</span>
                @endif
            </p>
            <div class="space-y-2">
                @foreach($item['question']->options as $oi => $option)
                    @php
                        $isCorrectOption = $oi === $item['question']->correct_option;
                        $isChosen = $oi === $item['chosen'];
                    @endphp
                    <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm {{ $isCorrectOption ? 'border-green-500 bg-green-50 dark:bg-green-500/10' : ($isChosen ? 'border-red-500 bg-red-50 dark:bg-red-500/10' : 'border-gray-200 dark:border-gray-800') }}">
                        <span>
                            @if($isCorrectOption) ✅
                            @elseif($isChosen) ❌
                            @else ⬜
                            @endif
                        </span>
                        <span>{{ $option }}</span>
                        @if($isChosen && $item['is_correct'])
                            <span class="ms-auto text-xs text-gray-400">your answer</span>
                        @endif
                        @if($isCorrectOption && ! $item['is_correct'])
                            <span class="ms-auto text-xs text-gray-400">correct answer</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
