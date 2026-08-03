@extends('layouts.app')
@section('title', $quiz->title . ' – Aref Academy')

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $quiz->lesson) }}">{{ $quiz->lesson->title }}</a>
    <span class="mx-1">/</span> {{ $quiz->title }}
</div>

@if(session('result'))
    <div class="card mb-6 {{ session('result')['passed'] ? 'border-green-500 text-green-600 dark:text-green-400' : 'border-red-500 text-red-600 dark:text-red-400' }}">
        You scored <strong>{{ session('result')['score'] }}/{{ session('result')['total'] }}</strong>
        — {{ session('result')['passed'] ? 'Passed! 🎉' : 'Not passed (need ' . $quiz->pass_score . '%). Try again!' }}
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <form method="POST" action="{{ route('quizzes.submit', $quiz) }}" class="space-y-4 lg:col-span-2">
        @csrf
        @foreach($quiz->questions as $i => $question)
            <div class="card">
                <p class="mb-3 font-medium">{{ $i + 1 }}. {{ $question->question_text }}</p>
                <div class="space-y-2">
                    @foreach($question->options as $oi => $option)
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:border-indigo-500 dark:border-gray-800">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $oi }}" class="accent-indigo-600">
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
        <button class="btn">Submit Quiz</button>
    </form>

    <aside class="card h-fit">
        <h2 class="mb-3 font-semibold">Your Attempts</h2>
        <ul class="space-y-2 text-sm">
            @forelse($attempts as $attempt)
                <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                    <span>{{ $attempt->score }}/{{ $attempt->total_questions }} ({{ $attempt->percentage() }}%)</span>
                    <span class="badge {{ $attempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                        {{ $attempt->passed ? 'Passed' : 'Failed' }}
                    </span>
                </li>
            @empty
                <li class="text-gray-500 dark:text-gray-400">No attempts yet.</li>
            @endforelse
        </ul>
    </aside>
</div>
@endsection
