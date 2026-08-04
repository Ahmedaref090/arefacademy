<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * How long after the deadline a browser submit is still accepted
     * (covers network latency of the JS auto-submit). Past this grace
     * window, only server-saved answers are graded.
     */
    protected const GRACE_SECONDS = 120;

    public function show(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        $quiz->load('lesson.course', 'questions');
        $this->authorizeAccess($user, $quiz);

        // Resume an in-progress attempt — or auto-submit it if the
        // server-side deadline passed while the student was away.
        $activeAttempt = $quiz->inProgressAttemptFor($user);

        if ($activeAttempt && $activeAttempt->isExpired()) {
            $this->finalizeAttempt($activeAttempt, $activeAttempt->answers ?? []);

            return redirect()->route('quizzes.result', $activeAttempt);
        }

        $attempts = $quiz->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->latest()
            ->get();

        return view('student.quizzes.show', [
            'quiz' => $quiz,
            'attempts' => $attempts,
            'activeAttempt' => $activeAttempt,
            'savedAnswers' => $activeAttempt?->answers ?? [],
            'remainingSeconds' => $activeAttempt?->remainingSeconds(),
            'attemptsLeft' => $quiz->attemptsLeftFor($user),
        ]);
    }

    /**
     * Begin a new attempt. The clock starts here (started_at) and is
     * enforced server-side from this moment on.
     */
    public function start(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $this->authorizeAccess($user, $quiz);

        // Already in progress? Resume it instead of creating a duplicate.
        if ($quiz->inProgressAttemptFor($user)) {
            return redirect()->route('quizzes.show', $quiz);
        }

        if ($quiz->attemptsLeftFor($user) === 0) {
            return redirect()->route('quizzes.show', $quiz)
                ->withErrors(['quiz' => 'You have used all allowed attempts for this quiz.']);
        }

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'total_questions' => $quiz->questions()->count(),
            'answers' => [],
            'started_at' => now(),
        ]);

        return redirect()->route('quizzes.show', $quiz);
    }

    /**
     * AJAX autosave: persist a single answer on the in-progress attempt.
     * This is what makes resume-after-exit restore answers, and what the
     * backend grades if the timer expires before a manual submit.
     */
    public function saveAnswer(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $this->authorizeAccess($user, $quiz);

        $data = $request->validate([
            'question_id' => ['required', 'integer'],
            'option' => ['required', 'integer', 'min:0'],
        ]);

        $attempt = $quiz->inProgressAttemptFor($user);
        abort_unless($attempt, 404);

        // Time ran out between page load and this save — finalize now and
        // tell the frontend where to go.
        if ($attempt->isExpired()) {
            $this->finalizeAttempt($attempt, $attempt->answers ?? []);

            return response()->json([
                'expired' => true,
                'redirect' => route('quizzes.result', $attempt),
            ], 409);
        }

        $question = $quiz->questions()->find($data['question_id']);
        abort_unless($question && $data['option'] < count($question->options), 422);

        $answers = $attempt->answers ?? [];
        $answers[$question->id] = (int) $data['option'];
        $attempt->update(['answers' => $answers]);

        return response()->noContent();
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = $request->user();
        $this->authorizeAccess($user, $quiz);

        $attempt = $quiz->inProgressAttemptFor($user);

        // Nothing in progress (e.g. double-submit) — just go back.
        if (! $attempt) {
            return redirect()->route('quizzes.show', $quiz);
        }

        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $saved = $attempt->answers ?? [];
        $posted = $data['answers'] ?? [];

        // Security: the server clock is authoritative. Within the grace
        // window we merge posted answers over saved ones; past it, only
        // answers autosaved in time are graded.
        $endsAt = $attempt->endsAt();
        $withinTime = $endsAt === null
            || now()->lessThanOrEqualTo($endsAt->copy()->addSeconds(self::GRACE_SECONDS));

        $this->finalizeAttempt($attempt, $withinTime ? array_merge($saved, $posted) : $saved);

        return redirect()->route('quizzes.result', $attempt);
    }

    /**
     * Review a finished attempt: per-question breakdown with the
     * student's choice vs. the correct answer.
     */
    public function result(Request $request, QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
        abort_unless($attempt->completed_at !== null, 403);

        $attempt->load('quiz.lesson.course', 'quiz.questions');
        $quiz = $attempt->quiz;
        $answers = $attempt->answers ?? [];

        // Pre-compute the review rows so the Blade template stays simple
        // (this also eliminates the nested-directive parse error).
        $review = $quiz->questions->map(function ($question) use ($answers) {
            $chosen = $answers[$question->id] ?? null;
            $chosen = $chosen !== null ? (int) $chosen : null;

            return [
                'question' => $question,
                'chosen' => $chosen,
                'is_correct' => $chosen !== null && $question->isCorrect($chosen),
            ];
        });

        return view('student.quizzes.result', [
            'attempt' => $attempt,
            'quiz' => $quiz,
            'review' => $review,
            'attemptsLeft' => $quiz->attemptsLeftFor($request->user()),
        ]);
    }

    /** Grade the answers and close the attempt. */
    protected function finalizeAttempt(QuizAttempt $attempt, array $answers): void
    {
        $questions = $attempt->quiz->questions()->get();

        $score = 0;
        foreach ($questions as $question) {
            $chosen = $answers[$question->id] ?? null;

            // Ignore out-of-range option indexes (tampered payloads).
            if ($chosen !== null && isset($question->options[(int) $chosen]) && $question->isCorrect((int) $chosen)) {
                $score++;
            }
        }

        $total = $questions->count();

        $attempt->update([
            'score' => $score,
            'total_questions' => $total,
            'passed' => $total > 0 && ($score / $total * 100) >= $attempt->quiz->pass_score,
            'answers' => $answers,
            'completed_at' => now(),
        ]);
    }

    protected function authorizeAccess(User $user, Quiz $quiz): void
    {
        abort_unless($user->isEnrolledIn($quiz->lesson->course) || $quiz->lesson->is_free, 403);
    }
}
