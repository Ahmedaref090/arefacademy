<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Enums\PurchaseStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'phone', 'parent_phone', 'email', 'avatar',
        'role', 'governorate', 'grade_level', 'password',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'grade_level' => GradeLevel::class,
            'phone_verified_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Scope: only student accounts (excludes admins).
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', UserRole::Student);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isStudent(): bool
    {
        return $this->role === UserRole::Student;
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    public function completedLessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')
            ->withPivot(['watch_seconds', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Lifetime courses this student has requested to buy.
     * Pivot "status": pending | approved | rejected.
     */
    public function purchasedCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Individual course months this student has requested to buy.
     * Pivot "status": pending | approved | rejected.
     */
    public function courseMonths(): BelongsToMany
    {
        return $this->belongsToMany(CourseMonth::class, 'course_month_user')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * The student's purchase status for a lifetime course, or null
     * when they have never requested it.
     */
    public function purchaseStatusForCourse(Course $course): ?PurchaseStatus
    {
        $purchase = $this->purchasedCourses()->where('courses.id', $course->id)->first();

        return $purchase ? PurchaseStatus::from($purchase->pivot->status) : null;
    }

    /**
     * The student's subscription status for a specific course month, or null
     * when they have never requested it.
     */
    public function purchaseStatusForMonth(CourseMonth $month): ?PurchaseStatus
    {
        $subscription = $this->courseMonths()->where('course_months.id', $month->id)->first();

        return $subscription ? PurchaseStatus::from($subscription->pivot->status) : null;
    }

    public function hasApprovedPurchaseFor(Course $course): bool
    {
        return $this->purchaseStatusForCourse($course) === PurchaseStatus::Approved;
    }

    public function hasApprovedPurchaseForMonth(CourseMonth $month): bool
    {
        return $this->purchaseStatusForMonth($month) === PurchaseStatus::Approved;
    }

    public function isEnrolledIn(Course $course): bool
    {
        return $this->enrollments()
            ->where('course_id', $course->id)
            ->where('status', EnrollmentStatus::Active)
            ->exists();
    }

    /**
     * The student's active enrollment record for a course, if any.
     * Note: an "active" enrollment may still be expired (expires_at in the past).
     */
    public function activeEnrollmentIn(Course $course): ?Enrollment
    {
        return $this->enrollments()
            ->where('course_id', $course->id)
            ->where('status', EnrollmentStatus::Active)
            ->latest('enrolled_at')
            ->first();
    }

    /**
     * True only when the student has an active AND non-expired subscription.
     * Enrollments with expires_at = null never expire (free courses, legacy).
     */
    public function hasActiveSubscriptionTo(Course $course): bool
    {
        $enrollment = $this->activeEnrollmentIn($course);

        return $enrollment !== null && ! $enrollment->isExpired();
    }

    /**
     * True when the student may watch a lesson's video:
     *
     * - Free-preview lessons are always accessible.
     * - An active (non-expired) full-course subscription unlocks everything.
     * - Otherwise, a lesson that belongs to a course month requires an
     *   APPROVED subscription for that specific month — pending months
     *   stay locked until the admin confirms the payment.
     */
    public function canAccessLesson(Lesson $lesson): bool
    {
        if ($lesson->is_free) {
            return true;
        }

        if ($this->hasActiveSubscriptionTo($lesson->course)) {
            return true;
        }

        if ($lesson->course_month_id) {
            return $this->courseMonths()
                ->where('course_months.id', $lesson->course_month_id)
                ->wherePivot('status', PurchaseStatus::Approved)
                ->exists();
        }

        return false;
    }

    public function markLessonCompleted(Lesson $lesson): void
    {
        if ($this->completedLessons()->where('lesson_id', $lesson->id)->exists()) {
            $this->completedLessons()->updateExistingPivot($lesson->id, ['completed_at' => now()]);
        } else {
            $this->completedLessons()->attach($lesson->id, ['completed_at' => now()]);
        }
    }

    /**
     * Add watch time (in seconds) to the student's activity for a lesson.
     */
    public function recordWatchTime(Lesson $lesson, int $seconds): void
    {
        $exists = DB::table('lesson_user')
            ->where('user_id', $this->id)
            ->where('lesson_id', $lesson->id)
            ->exists();

        if ($exists) {
            DB::table('lesson_user')
                ->where('user_id', $this->id)
                ->where('lesson_id', $lesson->id)
                ->increment('watch_seconds', $seconds, ['updated_at' => now()]);
        } else {
            DB::table('lesson_user')->insert([
                'user_id' => $this->id,
                'lesson_id' => $lesson->id,
                'watch_seconds' => $seconds,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /** Public avatar URL, or null when the student hasn't uploaded one. */
    public function avatarUrl(): ?string
    {
        return $this->avatar ? Storage::disk('public')->url($this->avatar) : null;
    }

    /**
     * Human-readable membership duration, e.g. "3 months" / "٣ شهور".
     * Locale-aware: returns Arabic when the app locale is "ar".
     */
    public function membershipDuration(): string
    {
        $diff = $this->created_at->diff(now());

        if (app()->getLocale() === 'ar') {
            return $this->arabicDuration($diff);
        }

        return $this->created_at->diffForHumans(null, true);
    }

    /**
     * Build an Arabic human-readable duration from a DateInterval.
     */
    protected function arabicDuration(\DateInterval $diff): string
    {
        $units = [
            'y' => ['سنة', 'سنتين', 'سنين'],
            'm' => ['شهر', 'شهرين', 'شهور'],
            'd' => ['يوم', 'يومين', 'أيام'],
            'h' => ['ساعة', 'ساعتين', 'ساعات'],
            'i' => ['دقيقة', 'دقيقتين', 'دقايق'],
        ];

        foreach ($units as $prop => [$one, $two, $many]) {
            $value = $diff->$prop;
            if ($value < 1) {
                continue;
            }

            if ($value === 1) {
                return $one;
            }
            if ($value === 2) {
                return $two;
            }

            return $value . ' ' . ($value <= 10 ? $many : $one);
        }

        return 'دلوقتي';
    }

    /** Initials for the avatar fallback circle, e.g. "AR". */
    public function initials(): string
    {
        return collect(explode(' ', trim($this->name)))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');
    }
}
