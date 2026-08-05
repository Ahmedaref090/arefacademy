@extends('layouts.app')
@section('title', $lesson->title . ' – Aref Academy')

@section('content')
<div class="mb-4 text-sm text-gray-500 dark:text-gray-400">
    <a class="hover:text-indigo-600 dark:hover:text-indigo-400" href="{{ route('courses.show', $lesson->course) }}">{{ $lesson->course->title }}</a>
    <span class="mx-1">/</span> {{ $lesson->title }}
</div>

<div class="grid gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-2">

        {{-- Video player --}}
        <div class="card p-0">
            @if($lesson->embedUrl())
                <div class="aspect-video">
                    <iframe src="{{ $lesson->embedUrl() }}" class="h-full w-full rounded-t-xl" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"></iframe>
                </div>
            @elseif($lesson->videoSrc())
                {{-- video_path may be a full URL, a /storage/… path, or a path
                     relative to the public disk — videoSrc() resolves all three. --}}
                <video controls controlsList="nodownload" preload="metadata"
                    class="aspect-video w-full rounded-t-xl bg-black"
                    src="{{ $lesson->videoSrc() }}"></video>
            @else
                <div class="flex aspect-video items-center justify-center rounded-t-xl bg-gray-900 font-mono text-gray-500">// no video yet</div>
            @endif
            <div class="flex items-center justify-between p-4">
                <h1 class="font-bold">{{ $lesson->title }}</h1>
                @if($completed)
                    <span class="badge bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400">✓ Completed</span>
                @else
                    <form method="POST" action="{{ route('lessons.complete', $lesson) }}">
                        @csrf
                        <button class="btn">Mark Complete</button>
                    </form>
                @endif
            </div>
        </div>

        @if($lesson->description)
            <div class="card text-sm leading-relaxed text-gray-600 dark:text-gray-300">{!! nl2br(e($lesson->description)) !!}</div>
        @endif

        {{-- Resources --}}
        @if($lesson->attachments->isNotEmpty())
            <div class="card">
                <h2 class="mb-3 font-semibold">📎 Resources</h2>
                <ul class="space-y-2 text-sm">
                    @foreach($lesson->attachments as $attachment)
                        <li class="flex items-center justify-between rounded-lg border border-gray-200 px-3 py-2 dark:border-gray-800">
                            <span>{{ $attachment->title }} <span class="text-xs text-gray-400">({{ strtoupper($attachment->file_type) }} · {{ $attachment->humanSize() }})</span></span>
                            <a class="text-indigo-600 dark:text-indigo-400" href="{{ $attachment->downloadUrl() }}">Download</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Quizzes --}}
        @foreach($lesson->quizzes as $quiz)
            @php
                $best = $quiz->bestAttemptFor(auth()->user());
                $inProgress = $quiz->inProgressAttemptFor(auth()->user());
                $left = $quiz->attemptsLeftFor(auth()->user());
            @endphp
            <div class="card flex items-center justify-between">
                <div>
                    <div class="font-semibold">🧠 {{ $quiz->title }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ $quiz->questions->count() }} questions · pass at {{ $quiz->pass_score }}%
                        @if($quiz->time_limit_minutes) · ⏱ {{ $quiz->time_limit_minutes }} min @endif
                        @if($quiz->max_attempts) · {{ $quiz->max_attempts }} attempt(s) max @endif
                        @if($best) · best score: <span class="font-semibold">{{ $best->percentage() }}%</span> @endif
                    </div>
                </div>
                @if($inProgress)
                    <a class="btn" href="{{ route('quizzes.show', $quiz) }}">Resume Quiz</a>
                @elseif($left === 0)
                    <span class="badge bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400">No attempts left</span>
                @else
                    <a class="btn-secondary" href="{{ route('quizzes.show', $quiz) }}">{{ $best ? 'Retake' : 'Take Quiz' }}</a>
                @endif
            </div>
        @endforeach

        {{-- Assignments --}}
        @foreach($lesson->assignments as $assignment)
            @php($submission = $assignment->submissionFor(auth()->user()))
            <div class="card">
                <div class="mb-2 font-semibold">💻 {{ $assignment->title }}</div>
                <div class="mb-3 text-xs text-gray-500 dark:text-gray-400">
                    Max score: {{ $assignment->max_score }}
                    @if($assignment->deadline) · Deadline: {{ $assignment->deadline->format('Y-m-d H:i') }} @endif
                </div>
                @if($assignment->description)
                    <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{!! nl2br(e($assignment->description)) !!}</p>
                @endif

                @if($submission)
                    <div class="mb-4 rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-800">
                        <div class="font-medium">Your submission</div>
                        @if($submission->file_path)
                            <a class="text-indigo-600 dark:text-indigo-400" href="{{ route('submissions.download', $submission) }}">Download submitted file</a>
                        @endif
                        @if($submission->isGraded())
                            <div class="mt-1">Score: <span class="font-bold text-green-600 dark:text-green-400">{{ $submission->score }}/{{ $assignment->max_score }}</span></div>
                            @if($submission->feedback)<div class="mt-1 text-gray-500 dark:text-gray-400">Feedback: {{ $submission->feedback }}</div>@endif
                        @else
                            <div class="mt-1 text-amber-500">Awaiting grading…</div>
                        @endif
                    </div>
                @endif

                <form method="POST" action="{{ route('assignments.submit', $assignment) }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label">Upload file (optional)</label>
                        <input type="file" name="file" class="input">
                    </div>
                    <div>
                        <label class="label">Or paste your code</label>
                        <textarea name="code" rows="6" class="input font-mono" placeholder="// your solution here">{{ old('code', $submission->code ?? '') }}</textarea>
                    </div>
                    <button class="btn">{{ $submission ? 'Resubmit' : 'Submit Assignment' }}</button>
                </form>
            </div>
        @endforeach

        {{-- Prev / Next --}}
        <div class="flex justify-between text-sm">
            @if($prev)<a class="btn-secondary" href="{{ route('lessons.show', $prev) }}">← {{ Str::limit($prev->title, 24) }}</a>@else<span></span>@endif
            @if($next)<a class="btn-secondary" href="{{ route('lessons.show', $next) }}">{{ Str::limit($next->title, 24) }} →</a>@endif
        </div>
    </div>

    {{-- Lesson list --}}
    <aside class="card h-fit">
        <h2 class="mb-3 font-semibold">Lessons</h2>
        <ul class="space-y-1 text-sm">
            @foreach($lesson->course->lessons as $l)
                <li>
                    <a href="{{ route('lessons.show', $l) }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 {{ $l->id === $lesson->id ? 'bg-indigo-50 font-semibold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400' : 'hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        <span>{{ in_array($l->id, $completedIds) ? '✅' : '⬜' }}</span>
                        <span class="truncate">{{ $l->title }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </aside>
</div>

<script>
    // Watch-time tracking: ping the server every 30s while the tab is visible.
    (function () {
        const url = @json(route('lessons.progress', $lesson));
        const token = @json(csrf_token());

        setInterval(() => {
            if (document.hidden) return;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ seconds: 30 }),
                keepalive: true,
            }).catch(() => {});
        }, 30000);
    })();
</script>
@endsection
