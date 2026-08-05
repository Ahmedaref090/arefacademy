<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson)
    {
        // LessonPolicy::view — requires an approved purchase (course_user /
        // course_month_user), an active legacy enrollment, an admin, or a
        // free preview lesson. Everyone else gets a 403.
        Gate::authorize('view', $lesson);

        $user = $request->user();

        $lesson->load(['course.lessons', 'attachments', 'quizzes.questions', 'assignments']);

        $course = $lesson->course;

        // "enrolled" = paid/approved access (not merely a free preview) —
        // the view uses it to toggle purchase prompts vs. full-content UI.
        $enrolled = $user->isAdmin()
            || $user->hasActiveSubscriptionTo($course) // legacy enrollment system
            || $user->hasApprovedPurchaseFor($course)
            || ($course->isPerMonth()
                && $lesson->month !== null
                && $user->hasApprovedPurchaseForMonth($lesson->month));

        $lessons = $course->lessons;
        $index = $lessons->search(fn ($l) => $l->id === $lesson->id);
        $prev = $index > 0 ? $lessons[$index - 1] : null;
        $next = $index !== false && $index < $lessons->count() - 1 ? $lessons[$index + 1] : null;

        $completedIds = $user->completedLessons()
            ->whereNotNull('lesson_user.completed_at')
            ->pluck('lesson_id')
            ->all();

        return view('student.lessons.show', [
            'lesson' => $lesson,
            'enrolled' => $enrolled,
            'completed' => in_array($lesson->id, $completedIds),
            'completedIds' => $completedIds,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        Gate::authorize('view', $lesson);

        $request->user()->markLessonCompleted($lesson);

        return back()->with('status', 'Lesson marked as complete.');
    }

    /**
     * Watch-time heartbeat — the lesson page pings this every 30s
     * while the tab is visible. Feeds the activity stats.
     */
    public function progress(Request $request, Lesson $lesson)
    {
        Gate::authorize('view', $lesson);

        $data = $request->validate([
            'seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $request->user()->recordWatchTime($lesson, $data['seconds']);

        return response()->noContent();
    }
}
