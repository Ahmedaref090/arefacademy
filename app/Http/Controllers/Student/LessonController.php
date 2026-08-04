<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        $lesson->load(['course.lessons', 'attachments', 'quizzes.questions', 'assignments']);

        $enrolled = $user->isEnrolledIn($lesson->course);
        abort_unless($enrolled || $lesson->is_free || $user->isAdmin(), 403);

        $lessons = $lesson->course->lessons;
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
        $user = $request->user();

        abort_unless($user->isEnrolledIn($lesson->course) || $lesson->is_free, 403);

        $user->markLessonCompleted($lesson);

        return back()->with('status', 'Lesson marked as complete.');
    }

    /**
     * Watch-time heartbeat — the lesson page pings this every 30s
     * while the tab is visible. Feeds the activity stats.
     */
    public function progress(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        abort_unless($user->isEnrolledIn($lesson->course) || $lesson->is_free, 403);

        $data = $request->validate([
            'seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $user->recordWatchTime($lesson, $data['seconds']);

        return response()->noContent();
    }
}
