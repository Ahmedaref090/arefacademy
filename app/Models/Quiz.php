<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    use HasFactory;
    use HasTranslations;

    /** Columns stored as translatable JSON objects ({ar, en}). */
    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'lesson_id', 'title', 'description', 'pass_score',
        'time_limit_minutes', 'max_attempts',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
        ];
    }

    public function getTitleAttribute(): string
    {
        return $this->getTranslation('title');
    }

    public function getDescriptionAttribute(): string
    {
        return $this->getTranslation('description');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function bestAttemptFor(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->orderByDesc('score')
            ->first();
    }

    /** The student's unfinished attempt (completed_at = null), if any. */
    public function inProgressAttemptFor(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->latest('started_at')
            ->first();
    }

    /**
     * Remaining attempts for the student.
     * Returns null when the quiz allows unlimited attempts.
     */
    public function attemptsLeftFor(User $user): ?int
    {
        if ($this->max_attempts === null) {
            return null;
        }

        $used = $this->attempts()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        return max(0, $this->max_attempts - $used);
    }

    /** Admin analytics: attempts / average score % / pass rate %. */
    public function stats(): array
    {
        $attempts = $this->attempts()
            ->whereNotNull('completed_at')
            ->selectRaw('COUNT(*) as total, AVG(CASE WHEN total_questions > 0 THEN score / total_questions * 100 END) as avg_score, SUM(CASE WHEN passed = 1 THEN 1 ELSE 0 END) as passed_count')
            ->first();

        $total = (int) ($attempts->total ?? 0);

        return [
            'attempts' => $total,
            'avg_score' => (int) round($attempts->avg_score ?? 0),
            'pass_rate' => $total > 0 ? (int) round(($attempts->passed_count / $total) * 100) : 0,
        ];
    }
}
