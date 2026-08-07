@extends('layouts.account')
@section('title', __('Exam Results – Aref Academy'))

@section('account')
<h1 class="mb-6 text-2xl font-bold">{{ __('Exam Results') }}</h1>

{{-- Stats --}}
<div class="mb-6 grid grid-cols-3 gap-4">
    <div class="card"><div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">{{ $stats['total'] }}</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Attempts') }}</div></div>
    <div class="card"><div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['passed'] }}</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Passed') }}</div></div>
    <div class="card"><div class="text-2xl font-bold text-amber-500">{{ $stats['avg'] }}%</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Average Score') }}</div></div>
</div>

<div class="card p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">{{ __('Quiz') }}</th>
                    <th class="table-th">{{ __('Course') }}</th>
                    <th class="table-th">{{ __('Score') }}</th>
                    <th class="table-th">{{ __('Result') }}</th>
                    <th class="table-th">{{ __('Date') }}</th>
                    <th class="table-th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($attempts as $attempt)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td font-medium">{{ $attempt->quiz->title }}</td>
                        <td class="table-td text-gray-500 dark:text-gray-400">{{ $attempt->quiz->lesson->course->title }}</td>
                        <td class="table-td font-mono">{{ $attempt->score }}/{{ $attempt->total_questions }} ({{ $attempt->percentage() }}%)</td>
                        <td class="table-td">
                            <span class="badge {{ $attempt->passed ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ $attempt->passed ? __('Passed') : __('Failed') }}
                            </span>
                        </td>
                        <td class="table-td text-gray-400">{{ $attempt->created_at->format('Y-m-d') }}</td>
                        <td class="table-td">
                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="{{ route('quizzes.result', $attempt) }}">{{ __('Review') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="6">{{ __('No exam attempts yet. Take a quiz inside any lesson!') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $attempts->links() }}</div>
@endsection
