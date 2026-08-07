<?php

namespace App\Http\Controllers\Student;

use App\Enums\PurchaseStatus;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function show(Request $request, Lesson $lesson)
    {
        $user = $request->user();

        // Locked lesson? Deny gracefully (SweetAlert2 modal) instead of
        // Laravel's default 403 page.
        if (! $user->canAccessLesson($lesson)) {
            return $this->denyLockedLesson($request, $lesson);
        }

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

        // Sidebar lock states, computed once — avoids an N+1 from calling
        // canAccessLesson() per lesson inside the Blade loop.
        $hasActiveSubscription = $user->hasActiveSubscriptionTo($course);

        $approvedMonthIds = $user->courseMonths()
            ->where('course_months.course_id', $course->id)
            ->wherePivot('status', PurchaseStatus::Approved)
            ->pluck('course_months.id')
            ->all();

        $accessibleIds = $lessons
            ->filter(fn ($l) => $l->is_free
                || $hasActiveSubscription
                || ($l->course_month_id && in_array($l->course_month_id, $approvedMonthIds)))
            ->pluck('id')
            ->all();

        return view('student.lessons.show', [
            'lesson' => $lesson,
            'completed' => in_array($lesson->id, $completedIds),
            'completedIds' => $completedIds,
            'accessibleIds' => $accessibleIds,
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    /**
     * Secure R2 playback: returns a temporary (3-hour) signed URL for the
     * lesson video. Only students who can access the lesson may request it,
     * and the bucket stays private otherwise.
     */
    public function videoUrl(Request $request, Lesson $lesson)
    {
        abort_unless($request->user()->canAccessLesson($lesson), 403);
        abort_unless($lesson->isStoredOnR2(), 404);

        $disk = Storage::disk('r2');

        if (! $disk->exists($lesson->video_path)) {
            abort(404);
        }

        return response()->json([
            'url' => $disk->temporaryUrl($lesson->video_path, now()->addMinutes(180)),
            'expires_at' => now()->addMinutes(180)->toIso8601String(),
        ]);
    }

    public function complete(Request $request, Lesson $lesson)
    {
        abort_unless($request->user()->canAccessLesson($lesson), 403);

        $request->user()->markLessonCompleted($lesson);

        if ($request->expectsJson()) {
            $course = $lesson->course;
            $total = $course->lessons()->count();
            $done = $request->user()->completedLessons()
                ->whereIn('lesson_id', $course->lessons()->pluck('id'))
                ->count();

            return response()->json([
                'completed' => true,
                'lesson_id' => $lesson->id,
                'progress' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            ]);
        }

        return back()->with('status', __('Lesson marked as complete.'));
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

    /**
     * Gracefully deny access to a locked lesson:
     *
     * - AJAX/fetch calls receive a 403 JSON payload the frontend can catch
     *   and turn into the SweetAlert2 modal.
     * - Normal navigation is redirected back (falling back to the course
     *   page when there is no referer, or when the referer is the locked
     *   lesson itself — which would otherwise cause a redirect loop) with
     *   a flashed message the Blade view uses to fire the modal.
     */
    protected function denyLockedLesson(Request $request, Lesson $lesson)
    {
        $courseUrl = route('courses.show', $lesson->course);
        $message = __('messages.lesson_locked');

        if ($request->expectsJson()) {
            return response()->json([
                'locked' => true,
                'message' => $message,
                'course_url' => $courseUrl,
            ], 403);
        }

        $referer = $request->headers->get('referer');
        $target = ($referer && $referer !== $request->fullUrl()) ? $referer : $courseUrl;

        return redirect()->to($target)
            ->with('locked_error', $message)
            ->with('locked_course_url', $courseUrl);
    }
}
