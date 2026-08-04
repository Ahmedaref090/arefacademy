@extends('layouts.app')
@section('title', 'Edit ' . $quiz->title . ' – Aref Academy')

@section('content')
<h1 class="mb-6 text-2xl font-bold">Edit Quiz</h1>

{{-- Quiz analytics --}}
<div class="mb-6 grid max-w-3xl grid-cols-3 gap-4">
    <div class="card"><div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['attempts'] }}</div><div class="text-xs text-gray-500 dark:text-gray-400">Attempts</div></div>
    <div class="card"><div class="text-2xl font-bold text-amber-500">{{ $stats['avg_score'] }}%</div><div class="text-xs text-gray-500 dark:text-gray-400">Average Score</div></div>
    <div class="card"><div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['pass_rate'] }}%</div><div class="text-xs text-gray-500 dark:text-gray-400">Pass Rate</div></div>
</div>

<form method="POST" action="{{ route('admin.quizzes.update', $quiz) }}" class="max-w-3xl space-y-4">
    @csrf
    @method('PUT')
    @include('admin.quizzes._form')
    <button class="btn">Save Changes</button>
</form>

{{-- Recent attempts --}}
<div class="card mt-8 max-w-3xl p-0">
    <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">Recent Attempts</h2>
    <table class="w-full">
        <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Student</th><th class="table-th">Score</th><th class="table-th">Result</th><th class="table-th">Date</th></tr></thead>
        <tbody>
            @forelse($recentAttempts as $attempt)
                <tr class="border-b border-gray-100 dark:border-gray-800/50">
                    <td class="table-td">{{ $attempt->user->name }}</td>
                    <td class="table-td font-mono">{{ $attempt->score }}/{{ $attempt->total_questions }} ({{ $attempt->percentage() }}%)</td>
                    <td class="table-td"><span class="badge {{ $attempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">{{ $attempt->passed ? 'Passed' : 'Failed' }}</span></td>
                    <td class="table-td text-gray-400">{{ $attempt->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr><td class="table-td text-gray-400" colspan="4">No attempts yet.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
