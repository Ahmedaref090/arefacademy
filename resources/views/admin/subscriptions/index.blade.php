@extends('layouts.app')
@section('title', __('Monthly Subscriptions'))

@section('content')
@php
    $locale = app()->getLocale();
    $statusBadge = [
        'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'approved' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    ];
@endphp

<h1 class="mb-6 text-2xl font-bold">{{ __('Monthly Subscriptions') }}</h1>

{{-- Course (lifetime) subscription requests --}}
<div class="card mb-8">
    <h2 class="mb-4 font-semibold">{{ __('Full Courses') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left dark:border-gray-800">
                    <th class="px-3 py-2 font-semibold">{{ __('Student') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Course') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Request Date') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Status') }}</th>
                    <th class="px-3 py-2 font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($courseRequests as $request)
                    @php
                        $title = json_decode($request->course_title, true);
                        $title = is_array($title) ? ($title[$locale] ?? $title['ar'] ?? '') : $request->course_title;
                        $status = App\Enums\PurchaseStatus::tryFrom($request->status)?->label() ?? $request->status;
                        $pending = $request->status === 'pending';
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-3 py-2">{{ $request->student_name }}</td>
                        <td class="px-3 py-2">{{ $title }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $request->created_at }}</td>
                        <td class="px-3 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadge[$request->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $status }}</span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($pending)
                                <form method="POST" action="{{ route('admin.subscriptions.course.approve', $request->id) }}" class="inline">
                                    @csrf
                                    <button class="btn-secondary">{{ __('Approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscriptions.course.reject', $request->id) }}" class="inline" onsubmit="return confirm(@json(__('Reject')));">
                                    @csrf
                                    <button class="btn-danger">{{ __('Reject') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-6 text-center text-gray-400">{{ __('No courses yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $courseRequests->links('pagination::tailwind') }}</div>
</div>

{{-- Per-month subscription requests --}}
<div class="card">
    <h2 class="mb-4 font-semibold">{{ __('Monthly') }}</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 text-left dark:border-gray-800">
                    <th class="px-3 py-2 font-semibold">{{ __('Student') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Course') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Month') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Request Date') }}</th>
                    <th class="px-3 py-2 font-semibold">{{ __('Status') }}</th>
                    <th class="px-3 py-2 font-semibold"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($monthRequests as $request)
                    @php
                        $title = json_decode($request->course_title, true);
                        $title = is_array($title) ? ($title[$locale] ?? $title['ar'] ?? '') : $request->course_title;
                        $monthName = json_decode($request->month_name, true);
                        $monthName = is_array($monthName) ? ($monthName[$locale] ?? $monthName['ar'] ?? '') : $request->month_name;
                        $status = App\Enums\PurchaseStatus::tryFrom($request->status)?->label() ?? $request->status;
                        $pending = $request->status === 'pending';
                    @endphp
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="px-3 py-2">{{ $request->student_name }}</td>
                        <td class="px-3 py-2">{{ $title }}</td>
                        <td class="px-3 py-2">{{ $monthName }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $request->created_at }}</td>
                        <td class="px-3 py-2">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $statusBadge[$request->status] ?? 'bg-gray-100 text-gray-600' }}">{{ $status }}</span>
                        </td>
                        <td class="px-3 py-2 text-right">
                            @if($pending)
                                <form method="POST" action="{{ route('admin.subscriptions.month.approve', $request->id) }}" class="inline">
                                    @csrf
                                    <button class="btn-secondary">{{ __('Approve') }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.subscriptions.month.reject', $request->id) }}" class="inline" onsubmit="return confirm(@json(__('Reject')));">
                                    @csrf
                                    <button class="btn-danger">{{ __('Reject') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-6 text-center text-gray-400">{{ __('No months available yet.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $monthRequests->links('pagination::tailwind') }}</div>
</div>
@endsection
