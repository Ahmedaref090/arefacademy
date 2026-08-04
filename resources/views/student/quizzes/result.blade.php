@extends('layouts.app')
@section('title', 'Quiz Result – Aref Academy')

@section('content')
@php($quiz = $attempt->quiz)

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
            {{ $attempt->passed ? 'Passed! 🎉 Great work.' : 'Not passed — you need ' . $quiz->pass_score . '%. Review your answers below and retake.' }}
        </div>
    </div>
    <div class="flex gap-2">
        <a class="btn-secondary" href="{{ route('quizzes.show', $quiz) }}">Retake Quiz</a>
        <a class="btn" href="{{ route('lessons.show', $quiz->lesson) }}">Back to Lesson</a>
    </div>
</div>

{{-- Per-question review --}}
<div class="space-y-4">
    @foreach($quiz->questions as $i => $question)
        @php
            $chosen = $attempt->answers[$question->id] ?? null;
            $chosen = $chosen !== null ? (int) $chosen : null;
            $isCorrect = $chosen !== null && $question->isCorrect($chosen);
        @endphp
        <div class="card">
            <p class="mb-3 font-medium">
                {{ $i + 1 }}. {{ $question->question_text }}
                @if($isCorrect)
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">Correct</span>
                @else
                    <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">{{ $chosen === null ? 'Unanswered' : 'Wrong' }}</span>
                @endif
            </p>
            <div class="space-y-2">
                @foreach($question->options as $oi => $option)
                    <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-sm
                        {{ $oi === $question->correct_option ? 'border-green-500 bg-green-50 dark:bg-green-500/10' : '' }}
                        {{ $oi === $chosen && ! $isCorrect ? 'border-red-500 bg-red-50 dark:bg-red-500/10' : '' }}
                        {{ $oi !== $question->correct_option && $oi !== $chosen ? 'border-gray-200 dark:border-gray-800' : '' }}">
                        <span>
                            @if($oi === $question->correct_option) ✅
                            @elseif($oi === $chosen) ❌
                            @else ⬜
                            @endif
                        </span>
                        <span>{{ $option }}</span>
                        @if($oi === $chosen && $isCorrect)<span class="ml-auto text-xs text-gray-400">your answer</span>@endif
                        @if($oi === $question->correct_option && ! $isCorrect)<span class="ml-auto text-xs text-gray-400">correct answer</span>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
