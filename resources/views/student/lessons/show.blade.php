@extends('layouts.app')
@section('title', $lesson->title . ' – Aref Academy')

@section('content')
{{-- Soft gradient page backdrop (bleeds into the layout's main padding) --}}
<div class="-m-4 min-h-full bg-gradient-to-br from-slate-50 via-indigo-50/50 to-purple-50/70 p-4 md:-m-8 md:p-8 dark:from-gray-950 dark:via-indigo-950/20 dark:to-gray-950">

    {{-- Breadcrumb --}}
    <div class="mb-5 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a class="font-medium transition-colors duration-200 hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $lesson->course) }}">{{ $lesson->course->title }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5 text-gray-300 rtl:rotate-180 dark:text-gray-600"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        <span class="truncate font-semibold text-gray-700 dark:text-gray-200">{{ $lesson->title }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">

            {{-- ── Video player ─────────────────────────────────── --}}
            <div class="overflow-hidden rounded-2xl bg-gray-900 shadow-2xl shadow-indigo-500/25 ring-1 ring-black/10 dark:ring-white/10">
                {{-- Player header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 0 1 0 1.972l-11.54 6.347a1.125 1.125 0 0 1-1.667-.986V5.653Z" /></svg>
                        </span>
                        <div class="min-w-0">
                            <h1 class="truncate font-bold text-white">{{ $lesson->title }}</h1>
                            @if($lesson->duration_minutes)
                                <div class="flex items-center gap-1 text-xs text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                    {{ $lesson->duration_minutes }} min
                                </div>
                            @endif
                        </div>
                    </div>
                    @if($completed)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-400/30">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            Completed
                        </span>
                    @endif
                </div>

                {{-- Player body --}}
                @if($lesson->embedUrl())
                    <div class="aspect-video bg-black">
                        <iframe src="{{ $lesson->embedUrl() }}" class="h-full w-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                @elseif($lesson->videoSrc())
                    {{-- video_path may be a full URL, a /storage/… path, or a path
                         relative to the public disk — videoSrc() resolves all three. --}}
                    <video controls controlsList="nodownload" preload="metadata"
                        class="aspect-video w-full bg-black"
                        src="{{ $lesson->videoSrc() }}"></video>
                @else
                    <div class="flex aspect-video flex-col items-center justify-center gap-2 bg-gradient-to-br from-gray-800 to-gray-900 text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10"><path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                        <span class="font-mono text-sm">// no video yet</span>
                    </div>
                @endif

                {{-- Player footer --}}
                <div class="flex items-center justify-between gap-3 px-5 py-3.5">
                    <span class="text-xs text-gray-400">{{ $lesson->course->title }}</span>
                    @if(! $completed)
                        <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                            @csrf
                            <button class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:scale-105 hover:shadow-xl hover:shadow-indigo-500/40">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                Mark Complete
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ── Prev / Next navigation ───────────────────────── --}}
            <div class="flex items-center justify-between gap-3">
                @if($prev)
                    <a href="{{ route('lessons.show', $prev) }}"
                        class="group inline-flex max-w-[48%] items-center gap-2 rounded-full border border-indigo-200/80 bg-white/80 px-5 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm backdrop-blur transition-all duration-300 ease-in-out hover:-translate-x-1 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/20 rtl:hover:translate-x-1 dark:border-gray-700 dark:bg-gray-900/80 dark:text-indigo-400">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:-translate-x-0.5 rtl:rotate-180 rtl:group-hover:translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                        <span class="truncate">{{ Str::limit($prev->title, 24) }}</span>
                    </a>
                @else
                    <span></span>
                @endif
                @if($next)
                    <a href="{{ route('lessons.show', $next) }}"
                        class="group inline-flex max-w-[48%] items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:translate-x-1 hover:shadow-xl hover:shadow-indigo-500/50 rtl:hover:-translate-x-1">
                        <span class="truncate">{{ Str::limit($next->title, 24) }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                @endif
            </div>

            {{-- ── Description ──────────────────────────────────── --}}
            @if($lesson->description)
                <div class="rounded-2xl border border-white/60 bg-white/70 p-5 text-sm leading-relaxed text-gray-600 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70 dark:text-gray-300">
                    {!! nl2br(e($lesson->description)) !!}
                </div>
            @endif

            {{-- ── Resources ────────────────────────────────────── --}}
            @if($lesson->attachments->isNotEmpty())
                <div class="rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
                    <h2 class="mb-3 flex items-center gap-2 font-semibold">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-100 text-sm dark:bg-indigo-500/10">📎</span>
                        Resources
                    </h2>
                    <ul class="space-y-2 text-sm">
                        @foreach($lesson->attachments as $attachment)
                            <li class="flex items-center justify-between rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50">
                                <span>{{ $attachment->title }} <span class="text-xs text-gray-400">({{ strtoupper($attachment->file_type) }} · {{ $attachment->humanSize() }})</span></span>
                                <a class="font-medium text-indigo-600 transition-colors hover:text-indigo-500 dark:text-indigo-400" href="{{ $attachment->downloadUrl() }}">Download</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Quizzes ──────────────────────────────────────── --}}
            @foreach($lesson->quizzes as $quiz)
                @php
                    $best = $quiz->bestAttemptFor(auth()->user());
                    $inProgress = $quiz->inProgressAttemptFor(auth()->user());
                    $left = $quiz->attemptsLeftFor(auth()->user());
                @endphp
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
                    <div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-sm dark:bg-purple-500/10">🧠</span>
                            {{ $quiz->title }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ $quiz->questions->count() }} questions · pass at {{ $quiz->pass_score }}%
                            @if($quiz->time_limit_minutes) · ⏱ {{ $quiz->time_limit_minutes }} min @endif
                            @if($quiz->max_attempts) · {{ $quiz->max_attempts }} attempt(s) max @endif
                            @if($best) · best score: <span class="font-semibold">{{ $best->percentage() }}%</span> @endif
                        </div>
                    </div>
                    @if($inProgress)
                        <a class="inline-flex shrink-0 items-center rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:scale-105" href="{{ route('quizzes.show', $quiz) }}">Resume Quiz</a>
                    @elseif($left === 0)
                        <span class="badge shrink-0 bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">No attempts left</span>
                    @else
                        <a class="btn-secondary shrink-0 rounded-full" href="{{ route('quizzes.show', $quiz) }}">{{ $best ? 'Retake' : 'Take Quiz' }}</a>
                    @endif
                </div>
            @endforeach

            {{-- ── Assignments ──────────────────────────────────── --}}
            @foreach($lesson->assignments as $assignment)
                @php($submission = $assignment->submissionFor(auth()->user()))
                <div class="rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mb-2 flex items-center gap-2 font-semibold">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg