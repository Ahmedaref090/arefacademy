@extends('layouts.app')
@section('title', $user->name . ' – Aref Academy')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        <span class="font-mono" dir="ltr">{{ $user->phone }}</span> · {{ $user->governorate }} · {{ $user->grade_level?->label() }} · joined {{ $user->created_at->format('Y-m-d') }}
    </div>
</div>

<div class="grid gap-6 lg:grid-cols-2">
    <div class="card">
        <h2 class="mb-3 font-semibold">Enrollments & Progress</h2>
        <ul class="space-y-3 text-sm">
            @forelse($user->enrollments as $enrollment)
                @php($progress = $enrollment->course->progressFor($user))
                <li>
                    <div class="mb-1 flex items-center justify-between">
                        <span class="font-medium">{{ $enrollment->course->title }}</span>
                        <span class="badge {{ $enrollment->status === \App\Enums\EnrollmentStatus::Active ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400' }}">{{ $enrollment->status->label() }}</span>
                    </div>
                    <div class="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                        <div class="h-full rounded-full bg-indigo-600" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-400">{{ $progress }}% complete</div>
                </li>
            @empty
                <li class="text-gray-400">No enrollments.</li>
            @endforelse
        </ul>
    </div>

    <div class="card p-0">
        <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">Quiz Attempts</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Quiz</th><th class="table-th">Score</th><th class="table-th">Result</th><th class="table-th">Date</th></tr></thead>
            <tbody>
                @forelse($user->quizAttempts as $attempt)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td">{{ $attempt->quiz->title }}</td>
                        <td class="table-td font-mono">{{ $attempt->score }}/{{ $attempt->total_questions }}</td>
                        <td class="table-td"><span class="badge {{ $attempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">{{ $attempt->passed ? 'Passed' : 'Failed' }}</span></td>
                        <td class="table-td text-gray-400">{{ $attempt->created_at->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">No attempts.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card p-0 lg:col-span-2">
        <h2 class="border-b border-gray-200 p-4 font-semibold dark:border-gray-800">Assignment Submissions</h2>
        <table class="w-full">
            <thead><tr class="border-b border-gray-200 dark:border-gray-800"><th class="table-th">Assignment</th><th class="table-th">Course</th><th class="table-th">Score</th><th class="table-th">Submitted</th></tr></thead>
            <tbody>
                @forelse($user->submissions as $submission)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td">{{ $submission->assignment->title }}</td>
                        <td class="table-td">{{ $submission->assignment->lesson->course->title }}</td>
                        <td class="table-td font-mono">{{ $submission->isGraded() ? $submission->score . '/' . $submission->assignment->max_score : '—' }}</td>
                        <td class="table-td text-gray-400">{{ $submission->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="4">No submissions.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
