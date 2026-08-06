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

        // Free previews are open to everyone; everything else requires an
        // active full-course subscription or an approved subscription for
        // the lesson's specific month (per-month courses).
        abort_unless($user->canAccessLesson($lesson), 403);

        $lesson->load(['course.lessons', 'attachments', 'quizzes.questions', 'assignments']);

        $course = $lesson->course;

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
            'completed' => in_array($lesson->id, $completedIds),
            'completedIds' => $completedIds,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        abort_unless($request->user()->canAccessLesson($lesson), 403);

        $request->user()->markLessonCompleted($lesson);

        return back()->with('status', 'Lesson marked as complete.');
    }

    /**
     * Watch-time heartbeat — the lesson page pings this every 30s
     * while the tab is visible. Feeds the activity stats.
     */
    public function progress(Request $request, Lesson $lesson)
    {
        abort_unless($request->user()->canAccessLesson($lesson), 403);

        $data = $request->validate([
            'seconds' => ['required', 'integer', 'min:1', 'max:300'],
        ]);

        $request->user()->recordWatchTime($lesson, $data['seconds']);

        return response()->noContent();
    }
}
