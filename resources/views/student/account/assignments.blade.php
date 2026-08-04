@extends('layouts.account')
@section('title', 'Assignment Results – Aref Academy')

@section('account')
<h1 class="mb-6 text-2xl font-bold">Assignment Results</h1>

<div class="card p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">Assignment</th>
                    <th class="table-th">Course</th>
                    <th class="table-th">Submitted</th>
                    <th class="table-th">Status</th>
                    <th class="table-th">Score</th>
                    <th class="table-th">Feedback</th>
                    <th class="table-th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $submission)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td font-medium">{{ $submission->assignment->title }}</td>
                        <td class="table-td text-gray-500 dark:text-gray-400">{{ $submission->assignment->lesson->course->title }}</td>
                        <td class="table-td text-gray-400">{{ $submission->created_at->format('Y-m-d') }}</td>
                        <td class="table-td">
                            @if($submission->isGraded())
                                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">Graded</span>
                            @else
                                <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Pending</span>
                            @endif
                        </td>
                        <td class="table-td font-mono">{{ $submission->isGraded() ? $submission->score . '/' . $submission->assignment->max_score : '—' }}</td>
                        <td class="table-td max-w-48 truncate text-gray-500 dark:text-gray-400" title="{{ $submission->feedback }}">{{ $submission->feedback ?? '—' }}</td>
                        <td class="table-td">
                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="{{ route('lessons.show', $submission->assignment->lesson_id) }}">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="7">No assignment submissions yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $submissions->links() }}</div>
@endsection
