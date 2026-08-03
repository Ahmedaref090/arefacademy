<?php

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with('course.lessons')
            ->where('status', EnrollmentStatus::Active)
            ->get();

        $avgScore = $user->quizAttempts()
            ->where('total_questions', '>', 0)
            ->selectRaw('AVG(score / total_questions * 100) as avg')
            ->value('avg');

        // Lessons completed per day for the last 14 days (activity chart).
        $completed = $user->completedLessons()
            ->whereNotNull('lesson_user.completed_at')
            ->where('lesson_user.completed_at', '>=', now()->subDays(13)->startOfDay())
            ->get()
            ->groupBy(fn ($lesson) => $lesson->pivot->completed_at->toDateString())
            ->map->count();

        $chart = collect(range(13, 0))
            ->map(fn ($i) => now()->subDays($i)->toDateString())
            ->mapWithKeys(fn ($day) => [$day => $completed[$day] ?? 0]);

        return view('student.dashboard', [
            'enrollments' => $enrollments,
            'chart' => $chart,
            'stats' => [
                'courses' => $enrollments->count(),
                'completed_lessons' => $user->completedLessons()->whereNotNull('lesson_user.completed_at')->count(),
                'avg_quiz_score' => (int) round($avgScore ?? 0),
                'watch_minutes' => (int) round($user->completedLessons()->sum('lesson_user.watch_seconds') / 60),
            ],
        ]);
    }
}
