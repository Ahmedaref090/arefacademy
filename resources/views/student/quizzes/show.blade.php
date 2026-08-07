@extends('layouts.app')
@section('title', $quiz->title . ' – ' . __('Aref Academy'))

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('lessons.show', $quiz->lesson) }}">{{ $quiz->lesson->title }}</a>
    <span class="mx-1">/</span> {{ $quiz->title }}
</div>

@if($activeAttempt)
    {{-- ══ Active attempt: persistent timer + live side navigation ══ --}}
    <div x-data="quizPlayer">
        @if($quiz->time_limit_minutes)
            <div class="card mb-4 flex items-center justify-between border-amber-500">
                <span class="text-sm font-medium">{{ __('⏱ Time remaining — the attempt auto-submits when time runs out.') }}</span>
                <span class="font-mono text-xl font-bold" :class="remaining <= 60 ? 'text-red-500' : 'text-amber-500'" x-text="display"></span>
            </div>
        @endif

        <div class="grid items-start gap-6 lg:grid-cols-3">
            {{-- Questions --}}
            <form x-ref="quizForm" method="POST" action="{{ route('quizzes.submit', $quiz) }}" class="space-y-4 lg:col-span-2">
                @csrf
                @foreach($quiz->questions as $i => $question)
                    <div class="card scroll-mt-24" id="q{{ $question->id }}">
                        <p class="mb-3 font-medium">
                            <span class="me-1 font-mono text-xs text-gray-400">{{ $i + 1 }}/{{ $quiz->questions->count() }}</span>
                            {{ $question->question_text }}
                        </p>
                        <div class="space-y-2">
                            @foreach($question->options as $oi => $option)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm transition-colors hover:border-indigo-500 dark:border-gray-800">
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $oi }}"
                                           class="accent-indigo-600"
                                           @checked(isset($savedAnswers[$question->id]) && (int) $savedAnswers[$question->id] === $oi)
                                           @change="answer({{ $question->id }}, {{ $oi }})">
                                    <span>{{ $option }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <button class="btn" :disabled="submitting">{{ __('Submit Quiz') }}</button>
            </form>

            {{-- Live side navigation & progress tracker --}}
            <aside class="card h-fit lg:sticky lg:top-6">
                <h2 class="mb-3 font-semibold">{{ __('Progress') }}</h2>

                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ __('Answered') }}</span>
                    <span class="font-mono font-bold"><span x-text="answered.length"></span>/{{ $quiz->questions->count() }}</span>
                </div>
                <div class="mb-4 h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-800">
                    <div class="h-full rounded-full bg-emerald-500 transition-all duration-300"
                         :style="'width: ' + (answered.length / {{ max(1, $quiz->questions->count()) }} * 100) + '%'"></div>
                </div>

                <div class="mb-4 grid grid-cols-5 gap-2">
                    @foreach($quiz->questions as $i => $question)
                        <button type="button"
                                @click="go({{ $question->id }})"
                                :class="answered.includes({{ $question->id }})
                                    ? 'border-emerald-500 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                    : 'border-gray-200 text-gray-500 hover:border-indigo-400 dark:border-gray-700 dark:text-gray-400'"
                                class="flex h-9 items-center justify-center rounded-lg border text-sm font-semibold transition-colors">
                            {{ $i + 1 }}
                        </button>
                    @endforeach
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 pt-3 text-xs text-gray-400 dark:border-gray-800">
                    <span x-text="saveStatus"></span>
                    <span>{{ __('Attempt #:n', ['n' => $attempts->count() + 1]) }}</span>
                </div>
            </aside>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('quizPlayer', () => ({
                remaining: {{ (int) ($remainingSeconds ?? 0) }},
                hasTimer: {{ $quiz->time_limit_minutes ? 'true' : 'false' }},
                answered: @json(array_map('intval', array_keys($savedAnswers))),
                saveStatus: '',
                submitting: false,

                get display() {
                    const safe = Math.max(this.remaining, 0);
                    const h = Math.floor(safe / 3600);
                    const m = Math.floor((safe % 3600) / 60);
                    const s = safe % 60;
                    const mm = String(m).padStart(2, '0');
                    const ss = String(s).padStart(2, '0');
                    return h > 0 ? h + ':' + mm + ':' + ss : mm + ':' + ss;
                },

                init() {
                    if (! this.hasTimer || this.remaining <= 0) return;

                    const timer = setInterval(() => {
                        this.remaining--;
                        if (this.remaining <= 0) {
                            clearInterval(timer);
                            this.submitForm();
                        }
                    }, 1000);
                },

                go(id) {
                    document.getElementById('q' + id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                },

                answer(questionId, option) {
                    if (! this.answered.includes(questionId)) {
                        this.answered.push(questionId);
                    }

                    this.saveStatus = @json(__('Saving…'));

                    fetch(@json(route('quizzes.answer', $quiz)), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': @json(csrf_token()),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ question_id: questionId, option: option }),
                    }).then(async (response) => {
                        if (response.status === 409) {
                            // Timer expired server-side — attempt was auto-graded.
                            const data = await response.json();
                            window.location.href = data.redirect;
                            return;
                        }
                        this.saveStatus = @json(__('All answers saved ✓'));
                    }).catch(() => {
                        this.saveStatus = @json(__('Offline — your answers still submit normally.'));
                    });
                },

                submitForm() {
                    if (this.submitting) return;
                    this.submitting = true;
                    this.$refs.quizForm.requestSubmit();
                },
            }));
        });
    </script>

    {{-- ══ In-quiz lock: once started, the student cannot navigate the platform until finished/time-up. ══ --}}
    <script>
        (function () {
            if (document.body.classList.contains('quiz-lock')) return;
            document.body.classList.add('quiz-lock');

            let locked = true;
            let warningShown = false;

            function requestFullscreen() {
                const el = document.documentElement;
                const req = el.requestFullscreen || el.webkitRequestFullscreen
                    || el.msRequestFullscreen || el.mozRequestFullScreen;
                if (req && !document.fullscreenElement) {
                    try { req.call(el); } catch (e) {}
                }
            }

            function exitFullscreen() {
                const doc = document;
                const ex = doc.exitFullscreen || doc.webkitExitFullscreen
                    || doc.msExitFullscreen || doc.mozCancelFullScreen;
                if (ex && doc.fullscreenElement) {
                    try { ex.call(doc); } catch (e) {}
                }
            }

            function showWarning() {
                if (warningShown) return;
                warningShown = true;
                requestFullscreen();
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: @json(__('quiz_lock_title')),
                        text: @json(__('quiz_lock_message')),
                        confirmButtonColor: '#6d38f6',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didClose: () => { warningShown = false; },
                    });
                }
            }

            // Enter fullscreen once engaged (re-entered on any interaction below).
            requestFullscreen();

            // Any in-page click re-enters fullscreen (the browser drops it on Esc).
            document.addEventListener('click', requestFullscreen);

            // Leaving the tab / switching window while the exam is live.
            let awayAt = 0;
            function onVisibility() {
                if (document.hidden) {
                    awayAt = Date.now();
                } else if (awayAt && awayAt > Date.now() - 500) {
                    return; // ignore the initial fullscreen handoff
                } else {
                    showWarning();
                }
            }
            document.addEventListener('visibilitychange', onVisibility);
            window.addEventListener('blur', function (e) {
                requestFullscreen();
                if (document.visibilityState === 'visible') {
                    showWarning();
                }
            });

            // If the user drops out of fullscreen (Esc), warn + force it back.
            document.addEventListener('fullscreenchange', () => {
                if (!document.fullscreenElement && locked) showWarning();
            });

            // On mount the lock is OFF so the first fullscreen is user-initiated;
            // flip it on after a brief moment so Esc afterward triggers the warning.
            setTimeout(() => { locked = true; }, 1500);
        })();
    </script>

    <style>
        /* While the quiz is live, hide the platform sidebar + top nav so the
           student cannot click away to other sections. Layout-specific selectors:
           the app sidebar is the fixed-width <aside>, and the top bar is the
           sticky <header>. The quiz's own sticky-progress aside is left alone. */
        body.quiz-lock aside.w-64 { display: none !important; }
        body.quiz-lock header { display: none !important; }
    </style>
@else
    {{-- ══ Intro / attempts-exhausted states ══ --}}
    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <h1 class="mb-2 text-2xl font-bold">{{ $quiz->title }}</h1>
            @if($quiz->description)
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{!! nl2br(e($quiz->description)) !!}</p>
            @endif

            <div class="mb-6 flex flex-wrap gap-2 text-xs">
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ __(':count questions', ['count' => $quiz->questions->count()]) }}</span>
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ __('Pass at :score%', ['score' => $quiz->pass_score]) }}</span>
                @if($quiz->time_limit_minutes)
                    <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">⏱ {{ $quiz->time_limit_minutes }} {{ __('minutes') }}</span>
                @endif
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $quiz->max_attempts ? $quiz->max_attempts . ' ' . __('attempt(s) max') : __('Unlimited attempts') }}
                </span>
            </div>

            @if($attemptsLeft === 0)
                <div class="rounded-lg border border-red-500 p-4 text-sm text-red-600 dark:text-red-400">
                    {{ __('You have used all :count allowed attempt(s) for this quiz.', ['count' => $quiz->max_attempts]) }}
                </div>
            @else
                <form method="POST" action="{{ route('quizzes.start', $quiz) }}">
                    @csrf
                    <button class="btn">{{ __('Start Quiz') }}</button>
                    @if($quiz->time_limit_minutes)
                        <p class="mt-2 text-xs text-gray-400">{{ __('The timer starts when you begin and keeps running even if you leave — you can come back and resume with the remaining time.') }}</p>
                    @endif
                </form>
            @endif
        </div>

        <aside class="card h-fit">
            <h2 class="mb-3 font-semibold">{{ __('Your Attempts') }}</h2>
            <ul class="space-y-2 text-sm">
                @forelse($attempts as $pastAttempt)
                    <li class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                        <span>{{ $pastAttempt->score }}/{{ $pastAttempt->total_questions }} ({{ $pastAttempt->percentage() }}%)</span>
                        <span class="flex items-center gap-2">
                            <span class="badge {{ $pastAttempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ $pastAttempt->passed ? __('Passed') : __('Failed') }}
                            </span>
                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="{{ route('quizzes.result', $pastAttempt) }}">{{ __('Review') }}</a>
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500 dark:text-gray-400">{{ __('No attempts yet.') }}</li>
                @endforelse
            </ul>
        </aside>
    </div>
@endif
@endsection
