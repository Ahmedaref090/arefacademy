@extends('layouts.app')
@section('title', __('All Courses – Aref Academy'))

@section('content')
<h1 class="mb-6 text-2xl font-bold">{{ __('All Courses') }}</h1>

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @forelse($courses as $course)
        @php
            $enrollment = $course->enrollments->first();
            $hasDiscount = ! is_null($course->sale_price) && (float) $course->sale_price < (float) $course->price;
            $effectivePrice = $hasDiscount ? (float) $course->sale_price : (float) $course->price;
        @endphp
        <a href="{{ route('courses.show', $course) }}" class="card block hover:border-indigo-500">
            @if($course->thumbnailUrl())
                <img src="{{ $course->thumbnailUrl() }}" alt="" class="mb-3 h-52 w-full rounded-lg object-cover">
            @else
                <div class="mb-3 flex h-52 items-center justify-center rounded-lg bg-gray-800 font-mono text-3xl text-indigo-400">&lt;/&gt;</div>
            @endif
            <div class="mb-1 flex items-center justify-between gap-2">
                <span class="font-semibold">{{ $course->title }}</span>
                @if($enrollment?->status === \App\Enums\EnrollmentStatus::Active)
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">{{ __('Enrolled') }}</span>
                @elseif($enrollment)
                    <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">{{ __('Pending') }}</span>
                @endif
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ __(':count lessons', ['count' => $course->lessons_count]) }}
                @if($course->grade_level) · {{ $course->grade_level->label() }} @endif
                @if($course->duration_weeks) · {{ __(':count weeks', ['count' => $course->duration_weeks]) }} @endif
            </div>
            <div class="mt-2 flex flex-wrap items-baseline gap-x-2">
                @if($hasDiscount)
                    <span class="font-mono text-sm text-gray-400 line-through">{{ number_format($course->price, 2) }} {{ __('EGP') }}</span>
                    <span class="font-mono font-extrabold text-red-600 dark:text-red-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @else
                    <span class="font-mono font-bold text-indigo-600 dark:text-indigo-400">
                        {{ $effectivePrice > 0 ? number_format($effectivePrice, 2) . ' ' . __('EGP') : __('Free') }}
                    </span>
                @endif
            </div>
        </a>
    @empty
        <div class="card text-sm text-gray-500 dark:text-gray-400">{{ __('No courses published yet.') }}</div>
    @endforelse
</div>

<div class="mt-6">{{ $courses->links() }}</div>
@endsection