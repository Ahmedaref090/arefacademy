@extends('layouts.account')
@section('title', __('Video Views – Aref Academy'))

@section('account')
<h1 class="mb-6 text-2xl font-bold">{{ __('Video Views') }}</h1>

{{-- Stats --}}
<div class="mb-6 grid grid-cols-2 gap-4">
    <div class="card"><div class="text-2xl font-bold text-sky-500">{{ $stats['minutes'] }}</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Minutes Watched') }}</div></div>
    <div class="card"><div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ $stats['completed'] }}</div><div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Lessons Completed') }}</div></div>
</div>

<div class="card p-0">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-800">
                    <th class="table-th">{{ __('Lesson') }}</th>
                    <th class="table-th">{{ __('Course') }}</th>
                    <th class="table-th">{{ __('Watch Time') }}</th>
                    <th class="table-th">{{ __('Status') }}</th>
                    <th class="table-th">{{ __('Last Watched') }}</th>
                    <th class="table-th"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($watched as $lesson)
                    <tr class="border-b border-gray-100 dark:border-gray-800/50">
                        <td class="table-td font-medium">{{ $lesson->title }}</td>
                        <td class="table-td text-gray-500 dark:text-gray-400">{{ $lesson->course->title }}</td>
                        <td class="table-td font-mono">{{ intdiv($lesson->pivot->watch_seconds, 60) }} {{ __('min') }}</td>
                        <td class="table-td">
                            @if($lesson->pivot->completed_at)
                                <span class="badge bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">{{ __('✓ Completed') }}</span>
                            @else
                                <span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">{{ __('Watching') }}</span>
                            @endif
                        </td>
                        <td class="table-td text-gray-400">{{ $lesson->pivot->updated_at->format('Y-m-d') }}</td>
                        <td class="table-td">
                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ __('Watch') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td class="table-td text-gray-400" colspan="6">{{ __('No videos watched yet. Start a lesson!') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-6">{{ $watched->links() }}</div>
@endsection
