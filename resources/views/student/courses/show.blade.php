@extends('layouts.app')
@section('title', $course->title . ' – Aref Academy')

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold">{{ $course->title }}</h1>
        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ $course->lessons->count() }} lessons
            @if($course->grade_level) · {{ $course->grade_level->label() }} @endif
            @if($course->duration_weeks) · {{ $course->duration_weeks }} weeks @endif
        </div>
    </div>
    <div class="text-right">
        <div class="font-mono text-xl font-bold text-indigo-600 dark:text-indigo-400">
            {{ (float) $course->price > 0 ? number_format($course->price, 2) . ' EGP' : 'Free' }}
        </div>
        @if(! $enrolled)
            <a href="{{ route('payments.checkout', $course) }}" class="btn mt-2">Enroll Now</a>
        @endif
    </div>
</div>

@if($pendingPayment)
    <div class="card mb-6 border-amber-500 text-sm">
        ⏳ You have a pending payment.
        @if($pendingPayment->fawry_reference_number)
            Fawry reference: <span class="font-mono font-bold">{{ $pendingPayment->fawry_reference_number }}</span>
        @endif
        <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('payments.show', $pendingPayment) }}">View details →</a>
    </div>
@endif

@if($course->description)
    <div class="card mb-6 text-sm leading-relaxed text-gray-600 dark:text-gray-300">{!! nl2br(e($course->description)) !!}</div>
@endif

<h2 class="mb-3 font-semibold">Course Content</h2>
<div class="card divide-y divide-gray-200 p-0 dark:divide-gray-800">
    @forelse($course->lessons as $i => $lesson)
        @php($locked = ! $enrolled && ! $lesson->is_free)
        <div class="flex items-center justify-between gap-3 px-5 py-3">
            <div class="flex items-center gap-3">
                <span class="font-mono text-xs text-gray-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                @if($locked)
                    <span class="text-gray-400 dark:text-gray-500">🔒 {{ $lesson->title }}</span>
                @else
                    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $lesson) }}">{{ $lesson->title }}</a>
                @endif
                @if($lesson->is_free)<span class="badge bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400">Free preview</span>@endif
            </div>
            <span class="text-xs text-gray-400">{{ $lesson->duration_minutes ? $lesson->duration_minutes . ' min' : '' }}</span>
        </div>
    @empty
        <div class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">No lessons yet.</div>
    @endforelse
</div>
@endsection
