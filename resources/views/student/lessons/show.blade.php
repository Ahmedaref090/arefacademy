@extends('layouts.app')
@section('title', $lesson->title . ' – ' . __('Aref Academy'))

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
                    <span id="player-header-completed" @if(! $completed) hidden @endif class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-400/30">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                        {{ __('Completed') }}
                    </span>
                </div>

                {{-- Player body --}}
                @if($lesson->embedUrl())
                    <div class="aspect-video bg-black">
                        <iframe src="{{ $lesson->embedUrl() }}" class="h-full w-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                    </div>
                @elseif($lesson->videoSrc())
                    {{-- video_path may be a full URL, a /storage/… path, or a path
                         relative to the public disk — videoSrc() resolves all three. --}}
                    <video controls controlsList="nodownload" preload="metadata" data-lesson-id="{{ $lesson->id }}"
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
                    <div id="complete-area" class="flex shrink-0">
                        @if($completed)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-400/30">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                {{ __('Completed') }}
                            </span>
                        @else
                            <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-gradient-to-r from-indigo-500 to-purple-500 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:scale-105 hover:shadow-xl hover:shadow-indigo-500/40">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                    {{ __('Mark Complete') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ── Prev / Next navigation ───────────────────────── --}}
            <div class="flex items-center justify-between gap-3">
                @if($prev)
                    @php($prevLocked = ! in_array($prev->id, $accessibleIds))
                    @if($prevLocked)
                        <button type="button" onclick="window.LockedModal.open(@json(route('courses.show', $lesson->course)))"
                            class="group inline-flex max-w-[48%] items-center gap-2 rounded-full border border-indigo-200/80 bg-white/80 px-5 py-2.5 text-sm font-semibold text-indigo-600/70 shadow-sm backdrop-blur transition-all duration-300 ease-in-out hover:-translate-x-1 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/20 rtl:hover:translate-x-1 dark:border-gray-700 dark:bg-gray-900/80 dark:text-indigo-400/70">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:-translate-x-0.5 rtl:rotate-180 rtl:group-hover:translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            <span class="truncate">{{ Str::limit($prev->title, 24) }}</span>
                        </button>
                    @else
                        <a href="{{ route('lessons.show', $prev) }}"
                            class="group inline-flex max-w-[48%] items-center gap-2 rounded-full border border-indigo-200/80 bg-white/80 px-5 py-2.5 text-sm font-semibold text-indigo-600 shadow-sm backdrop-blur transition-all duration-300 ease-in-out hover:-translate-x-1 hover:border-indigo-300 hover:shadow-lg hover:shadow-indigo-500/20 rtl:hover:translate-x-1 dark:border-gray-700 dark:bg-gray-900/80 dark:text-indigo-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:-translate-x-0.5 rtl:rotate-180 rtl:group-hover:translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                            <span class="truncate">{{ Str::limit($prev->title, 24) }}</span>
                        </a>
                    @endif
                @else
                    <span></span>
                @endif
                @if($next)
                    @php($nextLocked = ! in_array($next->id, $accessibleIds))
                    @if($nextLocked)
                        <button type="button" onclick="window.LockedModal.open(@json(route('courses.show', $lesson->course)))"
                            class="group inline-flex max-w-[48%] items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:translate-x-1 hover:shadow-xl hover:shadow-indigo-500/50 rtl:hover:-translate-x-1">
                            <span class="truncate">{{ Str::limit($next->title, 24) }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </button>
                    @else
                        <a href="{{ route('lessons.show', $next) }}"
                            class="group inline-flex max-w-[48%] items-center gap-2 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:translate-x-1 hover:shadow-xl hover:shadow-indigo-500/50 rtl:hover:-translate-x-1">
                            <span class="truncate">{{ Str::limit($next->title, 24) }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 shrink-0 transition-transform duration-300 group-hover:translate-x-0.5 rtl:rotate-180 rtl:group-hover:-translate-x-0.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                        </a>
                    @endif
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
                        {{ __('Resources') }}
                    </h2>
                    <ul class="space-y-2 text-sm">
                        @foreach($lesson->attachments as $attachment)
                            <li class="flex items-center justify-between rounded-xl border border-gray-200/80 bg-white/60 px-4 py-2.5 transition-all duration-300 ease-in-out hover:border-indigo-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50">
                                <span>{{ $attachment->title }} <span class="text-xs text-gray-400">({{ strtoupper($attachment->file_type) }} · {{ $attachment->humanSize() }})</span></span>
                                <a class="font-medium text-indigo-600 transition-colors hover:text-indigo-500 dark:text-indigo-400" href="{{ $attachment->downloadUrl() }}">{{ __('Download') }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- ── Quizzes ──────────────────────────────────────── --}}
            @foreach($lesson->quizzes as $quiz)
                @php($best = $quiz->bestAttemptFor(auth()->user()))
                @php($inProgress = $quiz->inProgressAttemptFor(auth()->user()))
                @php($left = $quiz->attemptsLeftFor(auth()->user()))
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
                    <div>
                        <div class="flex items-center gap-2 font-semibold">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-purple-100 text-sm dark:bg-purple-500/10">🧠</span>
                            {{ $quiz->title }}
                        </div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __(':count questions · pass at :score%', ['count' => $quiz->questions->count(), 'score' => $quiz->pass_score]) }}
                            @if($quiz->time_limit_minutes) · ⏱ {{ $quiz->time_limit_minutes }} {{ __('min') }} @endif
                            @if($quiz->max_attempts) · {{ $quiz->max_attempts }} {{ __('attempt(s) max') }} @endif
                            @if($best) · {{ __('best score:') }} <span class="font-semibold">{{ $best->percentage() }}%</span> @endif
                        </div>
                    </div>
                    @if($inProgress)
                        <a class="inline-flex shrink-0 items-center rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-2 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30 transition-all duration-300 ease-in-out hover:scale-105" href="{{ route('quizzes.show', $quiz) }}">{{ __('Resume Quiz') }}</a>
                    @elseif($left === 0)
                        <span class="badge shrink-0 bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">{{ __('No attempts left') }}</span>
                    @else
                        <a class="btn-secondary shrink-0 rounded-full" href="{{ route('quizzes.show', $quiz) }}">{{ $best ? __('Retake') : __('Take Quiz') }}</a>
                    @endif
                </div>
            @endforeach

            {{-- ── Assignments ──────────────────────────────────── --}}
            @foreach($lesson->assignments as $assignment)
                @php($submission = $assignment->submissionFor(auth()->user()))
                <div class="rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
                    <div class="mb-2 flex items-center gap-2 font-semibold">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-sm dark:bg-emerald-500/10">💻</span>
                        {{ $assignment->title }}
                    </div>
                    <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('Max score: :score', ['score' => $assignment->max_score]) }}
                        @if($assignment->deadline) · {{ __('Deadline: :date', ['date' => $assignment->deadline->format('Y-m-d H:i')]) }} @endif
                    </div>
                    @if($assignment->description)
                        <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{!! nl2br(e($assignment->description)) !!}</p>
                    @endif

                    @if($submission)
                        <div class="rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-800">
                            <div class="font-medium">{{ __('Your submission') }}</div>
                            @if($submission->file_path)
                                <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('submissions.download', $submission) }}">{{ __('Download submitted file') }}</a>
                            @endif
                            @if($submission->isGraded())
                                <div class="mt-1">{{ __('Score:') }} <span class="font-bold text-green-600 dark:text-green-400">{{ $submission->score }}/{{ $assignment->max_score }}</span></div>
                                @if($submission->feedback)<div class="mt-1 text-gray-500 dark:text-gray-400">{{ __('Feedback:') }} {{ $submission->feedback }}</div>@endif
                            @else
                                <div class="mt-1 text-amber-500">{{ __('Awaiting grading…') }}</div>
                            @endif
                        </div>
                    @else
                        <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="label">{{ __('Upload file (optional)') }}</label>
                                <input type="file" name="file" class="input">
                            </div>
                            <div>
                                <label class="label">{{ __('Or paste your code') }}</label>
                                <textarea name="code" rows="6" class="input font-mono" placeholder="{{ __('// your solution here') }}">{{ old('code') }}</textarea>
                            </div>
                            <button class="btn">{{ __('Submit Assignment') }}</button>
                        </form>
                    @endif
                </div>
            @endforeach

        </div>

        {{-- ── Sidebar: lesson list with lock states ─────────── --}}
        <aside class="h-fit rounded-2xl border border-white/60 bg-white/70 p-5 shadow-lg shadow-indigo-500/5 backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900/70">
            <h2 class="mb-3 flex items-center justify-between font-semibold">
                <span>{{ __('Lessons') }}</span>
                <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">{{ $lesson->course->lessons->count() }}</span>
            </h2>
            <ul class="space-y-1.5 text-sm">
                @foreach($lesson->course->lessons as $l)
                    @php($locked = ! in_array($l->id, $accessibleIds))
                    <li>
                        @if($locked)
                            {{-- Locked — dimmed but clickable, opens the subscription modal --}}
                            <button type="button"
                                onclick="window.LockedModal.open(@json(route('courses.show', $lesson->course)))"
                                class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-gray-400 opacity-75 transition-all duration-200 ease-in-out hover:scale-[1.02] hover:bg-gray-100 rtl:space-x-reverse dark:text-gray-500 dark:hover:bg-gray-800"
                                title="{{ __('This lesson is locked.') }}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                                <span class="truncate">{{ $l->title }}</span>
                            </button>
                        @elseif($l->id === $lesson->id)
                            {{-- Current lesson — gradient, glowing dot --}}
                            <span class="flex w-full items-center gap-2.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-500 px-3 py-2 font-bold text-white shadow-lg shadow-indigo-500/30 rtl:space-x-reverse">
                                <span class="relative flex h-2.5 w-2.5 shrink-0">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-white"></span>
                                </span>
                                <span class="truncate">{{ $l->title }}</span>
                            </span>
                        @else
                            <a href="{{ route('lessons.show', $l) }}" class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-gray-600 transition-all duration-200 ease-in-out hover:translate-x-0.5 hover:bg-gray-100 rtl:space-x-reverse rtl:hover:-translate-x-0.5 dark:text-gray-300 dark:hover:bg-gray-800">
                                <span class="text-sm leading-none">{{ in_array($l->id, $completedIds) ? '✅' : '⬜' }}</span>
                                <span class="truncate">{{ $l->title }}</span>
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>{{-- grid --}}
</div>{{-- backdrop --}}

<script>
    // Auto-complete a lesson when the student watches it to the end —
    // either the last 60 seconds, ≥90% played, or the `ended` event.
    (function () {
        const video = document.querySelector('video[data-lesson-id="{{ $lesson->id }}"]');
        if (! video) return; // no native player (e.g. YouTube embed) — skip

        const completeUrl = @json(route('lessons.complete', $lesson));
        const token = @json(csrf_token());
        const alreadyCompleted = @json((bool) $completed);

        let hasAutoCompleted = alreadyCompleted;
        let completing = false;

        function markComplete() {
            if (hasAutoCompleted || completing) return;
            completing = true;

            fetch(completeUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({}),
            })
                .then((res) => {
                    if (! res.ok) throw new Error('completion request failed');
                    return res.json();
                })
                .then((data) => {
                    if (data.completed) {
                        hasAutoCompleted = true;
                        applyCompletedUI();
                        showToast();
                    }
                })
                .catch(() => {
                    completing = false; // allow retry on the next tick
                });
        }

        function applyCompletedUI() {
            const header = document.getElementById('player-header-completed');
            if (header) header.hidden = false;

            const area = document.getElementById('complete-area');
            if (area) {
                area.innerHTML =
                    '<span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-400/15 px-3 py-1.5 text-xs font-semibold text-emerald-300 ring-1 ring-emerald-400/30">' +
                        '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>' +
                        'Completed' +
                    '</span>';
            }

            // Notify the rest of the page (e.g. a course progress bar) to refresh.
            window.dispatchEvent(new CustomEvent('lesson-completed', {
                detail: { lessonId: @json($lesson->id) },
            }));
        }

        function showToast() {
            const toastTitle = @json(__('Well done! You completed this lesson.'));
            if (! window.Swal) return;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: toastTitle,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                },
            });
        }

        function maybeAutoComplete() {
            if (hasAutoCompleted) return;
            const duration = video.duration;
            if (! Number.isFinite(duration) || duration <= 0) return;

            const remaining = duration - video.currentTime;
            const ratio = video.currentTime / duration;

            if (remaining <= 60 || ratio >= 0.9) markComplete();
        }

        video.addEventListener('timeupdate', maybeAutoComplete);
        video.addEventListener('ended', markComplete);
    })();
</script>
@endsection