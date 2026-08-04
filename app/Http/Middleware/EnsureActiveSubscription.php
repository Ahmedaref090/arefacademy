<?php

namespace App\Http\Middleware;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveSubscription
{
    /**
     * Lock course content (lessons, videos, quizzes, attachments) unless the
     * student has an active, non-expired subscription to the course.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins can always preview content.
        if ($user->isAdmin()) {
            return $next($request);
        }

        $lesson = $this->resolveLesson($request);
        $course = $this->resolveCourse($request, $lesson);

        // Route isn't bound to a course — nothing to check.
        if (! $course) {
            return $next($request);
        }

        // Free preview lessons (and their quizzes/attachments) stay open to everyone.
        if ($lesson?->is_free) {
            return $next($request);
        }

        if ($user->hasActiveSubscriptionTo($course)) {
            return $next($request);
        }

        $message = $user->isEnrolledIn($course)
            ? 'Your subscription to this course has expired. Renew your subscription to keep learning.'
            : 'This content is only available to enrolled students with an active subscription.';

        return redirect()
            ->route('courses.show', $course)
            ->with('subscription_locked', $message);
    }

    protected function resolveLesson(Request $request): ?Lesson
    {
        $lesson = $request->route('lesson');

        if ($lesson instanceof Lesson) {
            return $lesson;
        }

        $quiz = $request->route('quiz');

        if ($quiz instanceof Quiz) {
            return $quiz->lesson;
        }

        $attachment = $request->route('attachment');

        if ($attachment && method_exists($attachment, 'lesson')) {
            return $attachment->lesson;
        }

        return null;
    }

    protected function resolveCourse(Request $request, ?Lesson $lesson): ?Course
    {
        $course = $request->route('course');

        if ($course instanceof Course) {
            return $course;
        }

        return $lesson?->course;
    }
}
