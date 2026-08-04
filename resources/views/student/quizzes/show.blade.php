@extends('layouts.app')
@section('title', $quiz->title . ' – Aref Academy')

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
                <span class="text-sm font-medium">⏱ Time remaining — the attempt auto-submits when time runs out, even if you leave this page.</span>
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
                <button class="btn" :disabled="submitting">Submit Quiz</button>
            </form>

            {{-- Live side navigation & progress tracker --}}
            <aside class="card h-fit lg:sticky lg:top-6">
                <h2 class="mb-3 font-semibold">Progress</h2>

                <div class="mb-2 flex items-center justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Answered</span>
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
                    <span>Attempt #{{ $attempts->count() + 1 }}</span>
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

                    this.saveStatus = 'Saving…';

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
                        this.saveStatus = 'All answers saved ✓';
                    }).catch(() => {
                        this.saveStatus = 'Offline — your answers still submit normally.';
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
@else
    {{-- ══ Intro / attempts-exhausted states ══ --}}
    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="card lg:col-span-2">
            <h1 class="mb-2 text-2xl font-bold">{{ $quiz->title }}</h1>
            @if($quiz->description)
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{!! nl2br(e($quiz->description)) !!}</p>
            @endif

            <div class="mb-6 flex flex-wrap gap-2 text-xs">
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $quiz->questions->count() }} questions</span>
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">Pass at {{ $quiz->pass_score }}%</span>
                @if($quiz->time_limit_minutes)
                    <span class="badge bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">⏱ {{ $quiz->time_limit_minutes }} minutes</span>
                @endif
                <span class="badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $quiz->max_attempts ? $quiz->max_attempts . ' attempt(s) max' : 'Unlimited attempts' }}
                </span>
            </div>

            @if($attemptsLeft === 0)
                <div class="rounded-lg border border-red-500 p-4 text-sm text-red-600 dark:text-red-400">
                    You have used all {{ $quiz->max_attempts }} allowed attempt(s) for this quiz.
                </div>
            @else
                <form method="POST" action="{{ route('quizzes.start', $quiz) }}">
                    @csrf
                    <button class="btn">Start Quiz</button>
                    @if($quiz->time_limit_minutes)
                        <p class="mt-2 text-xs text-gray-400">The timer starts when you begin and keeps running even if you leave — you can come back and resume with the remaining time.</p>
                    @endif
                </form>
            @endif
        </div>

        <aside class="card h-fit">
            <h2 class="mb-3 font-semibold">Your Attempts</h2>
            <ul class="space-y-2 text-sm">
                @forelse($attempts as $pastAttempt)
                    <li class="flex items-center justify-between gap-2 rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                        <span>{{ $pastAttempt->score }}/{{ $pastAttempt->total_questions }} ({{ $pastAttempt->percentage() }}%)</span>
                        <span class="flex items-center gap-2">
                            <span class="badge {{ $pastAttempt->passed ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400' }}">
                                {{ $pastAttempt->passed ? 'Passed' : 'Failed' }}
                            </span>
                            <a class="text-indigo-600 hover:underline dark:text-indigo-400" href="{{ route('quizzes.result', $pastAttempt) }}">Review</a>
                        </span>
                    </li>
                @empty
                    <li class="text-gray-500 dark:text-gray-400">No attempts yet.</li>
                @endforelse
            </ul>
        </aside>
    </div>
@endif
@endsection
