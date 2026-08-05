<?php

namespace App\Policies;

use App\Enums\PurchaseStatus;
use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    /**
     * Admins (teachers) can watch everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * A student can watch a lesson only with an APPROVED purchase:
     * - lifetime course  -> approved row in course_user
     * - per-month course -> approved row in course_month_user
     *                       for the lesson's specific month
     */
    public function view(User $user, Lesson $lesson): bool
    {
        $course = $lesson->course;

        // LEGACY FALLBACK: the old Enrollment system (30-day subscriptions)
        // still grants access so existing students aren't locked out while
        // the hybrid purchase system rolls out. Remove this block once
        // enrollments are migrated to course_user / course_month_user.
        if ($user->hasActiveSubscriptionTo($course)) {
            return true;
        }

        // Free preview lessons are watchable by any logged-in student.
        if ($lesson->is_free) {
            return true;
        }

        if ($course->isLifetime()) {
            return $user->purchaseStatusForCourse($course) === PurchaseStatus::Approved;
        }

        // Per-month course: the lesson must be assigned to a month, and the
        // student must have an APPROVED subscription for that exact month.
        $month = $lesson->month;

        return $month !== null
            && $user->purchaseStatusForMonth($month) === PurchaseStatus::Approved;
    }
}
