<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function show(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        $quiz->load('lesson.course', 'questions');

        abort_unless($user->isEnrolledIn($quiz->lesson->course) || $quiz->lesson->is_free, 403);

        $attempts = $quiz->attempts()->where('user_id', $user->id)->latest()->get();

        return view('student.quizzes.show', compact('quiz', 'attempts'));
    }

    public function submit(Request $request, Quiz $quiz)
    {
        $user = $request->user();

        abort_unless($user->isEnrolledIn($quiz->lesson->course) || $quiz->lesson->is_free, 403);

        // Answers are nullable: the countdown timer may auto-submit
        // the form with some (or all) questions unanswered.
        $data = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $answers = $data['answers'] ?? [];
        $questions = $quiz->questions()->get();

        $score = 0;
        foreach ($questions as $question) {
            $chosen = $answers[$question->id] ?? null;
            if ($chosen !== null && $question->isCorrect((int) $chosen)) {
                $score++;
            }
        }

        $total = $questions->count();
        $passed = $total > 0 && ($score / $total * 100) >= $quiz->pass_score;

        QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'total_questions' => $total,
            'passed' => $passed,
            'answers' => $answers,
            'completed_at' => now(),
        ]);

        return redirect()->route('quizzes.show', $quiz)->with('result', [
            'score' => $score,
            'total' => $total,
            'passed' => $passed,
        ]);
    }
}
