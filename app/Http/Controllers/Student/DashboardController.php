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

        // ── Circular progress: exams (quizzes) ──────────────────────
        $attempts = $user->quizAttempts()->where('total_questions', '>', 0)->get();
        $examsTotal = $attempts->count();
        $examsPassed = $attempts->where('passed', true)->count();
        $avgScore = $examsTotal > 0
            ? (int) round($attempts->avg(fn ($a) => $a->score / $a->total_questions * 100))
            : 0;

        // ── Circular progress: lessons watched ──────────────────────
        $totalLessons = $enrollments->sum(fn ($e) => $e->course->lessons->count());
        $completedLessons = $user->completedLessons()->whereNotNull('lesson_user.completed_at')->count();
        $lessonsPercent = $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 0;

        // ── Average Grades chart (weekly = last 7 days, monthly = last 30) ──
        $chart = [
            'weekly' => $this->gradesSeries($user, 7),
            'monthly' => $this->gradesSeries($user, 30),
        ];

        // ── Invoices / subscriptions table ──────────────────────────
        $payments = $user->payments()
            ->with('course', 'courseMonth', 'courseMonths')
            ->latest()
            ->limit(10)
            ->get();

        return view('student.dashboard', [
            'enrollments' => $enrollments,
            'chart' => $chart,
            'payments' => $payments,
            'stats' => [
                'courses' => $enrollments->count(),
                'completed_lessons' => $completedLessons,
                'total_lessons' => $totalLessons,
                'lessons_percent' => $lessonsPercent,
                'exams_total' => $examsTotal,
                'exams_passed' => $examsPassed,
                'exams_percent' => $examsTotal > 0 ? (int) round(($examsPassed / $examsTotal) * 100) : 0,
                'avg_quiz_score' => $avgScore,
                'watch_minutes' => (int) round($user->completedLessons()->sum('lesson_user.watch_seconds') / 60),
            ],
        ]);
    }

    /**
     * Average quiz score (%) per day for the last N days.
     * Returns ['labels' => [...], 'data' => [...]] for the chart.
     */
    protected function gradesSeries($user, int $days): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        $byDay = $user->quizAttempts()
            ->where('total_questions', '>', 0)
            ->where('created_at', '>=', $from)
            ->get()
            ->groupBy(fn ($a) => $a->created_at->toDateString())
            ->map(fn ($group) => (int) round($group->avg(fn ($a) => $a->score / $a->total_questions * 100)));

        $labels = [];
        $data = [];

        foreach (range($days - 1, 0) as $i) {
            $day = now()->subDays($i);
            $labels[] = $day->format('d M');
            $data[] = $byDay[$day->toDateString()] ?? null; // null = gap in the line
        }

        return ['labels' => $labels, 'data' => $data];
    }
}
