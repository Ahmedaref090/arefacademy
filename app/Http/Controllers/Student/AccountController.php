<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Security & Login History — password form + recent logins.
     */
    public function security(Request $request)
    {
        $logins = $request->user()->loginHistories()
            ->latest()
            ->limit(20)
            ->get();

        return view('student.account.security', compact('logins'));
    }

    /**
     * Exam Results — every quiz attempt, fed by the auto-grading system.
     */
    public function examResults(Request $request)
    {
        $user = $request->user();

        $attempts = $user->quizAttempts()
            ->with('quiz.lesson.course')
            ->latest()
            ->paginate(15);

        $all = $user->quizAttempts()->where('total_questions', '>', 0)->get();

        $stats = [
            'total' => $all->count(),
            'passed' => $all->where('passed', true)->count(),
            'avg' => $all->count() > 0
                ? (int) round($all->avg(fn ($a) => $a->score / $a->total_questions * 100))
                : 0,
        ];

        return view('student.account.exams', compact('attempts', 'stats'));
    }

    /**
     * Assignment Results — homework submissions with grades & feedback.
     */
    public function assignmentResults(Request $request)
    {
        $submissions = $request->user()->submissions()
            ->with('assignment.lesson.course')
            ->latest()
            ->paginate(15);

        return view('student.account.assignments', compact('submissions'));
    }

    /**
     * Video Views — watch history from the lesson_user pivot,
     * fed by the 30-second watch-time heartbeat on lesson pages.
     */
    public function videoViews(Request $request)
    {
        $user = $request->user();

        $watched = $user->completedLessons()
            ->with('course')
            ->orderByPivot('updated_at', 'desc')
            ->paginate(15);

        $stats = [
            'minutes' => (int) round($user->completedLessons()->sum('lesson_user.watch_seconds') / 60),
            'completed' => $user->completedLessons()->whereNotNull('lesson_user.completed_at')->count(),
        ];

        return view('student.account.videos', compact('watched', 'stats'));
    }
}
