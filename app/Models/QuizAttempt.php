<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'user_id', 'score', 'total_questions',
        'passed', 'answers', 'started_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'passed' => 'boolean',
            'answers' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function percentage(): int
    {
        if ($this->total_questions === 0) {
            return 0;
        }

        return (int) round(($this->score / $this->total_questions) * 100);
    }

    /** An attempt is "in progress" until completed_at is set. */
    public function isInProgress(): bool
    {
        return $this->completed_at === null;
    }

    /**
     * The authoritative server-side deadline for this attempt.
     * Null when the quiz has no time limit.
     */
    public function endsAt(): ?Carbon
    {
        if (! $this->started_at || ! $this->quiz?->time_limit_minutes) {
            return null;
        }

        return $this->started_at->copy()->addMinutes($this->quiz->time_limit_minutes);
    }

    public function isExpired(): bool
    {
        $endsAt = $this->endsAt();

        return $endsAt !== null && now()->greaterThan($endsAt);
    }

    /** Seconds left on the clock (null when untimed). Drives the JS countdown. */
    public function remainingSeconds(): ?int
    {
        $endsAt = $this->endsAt();

        if ($endsAt === null) {
            return null;
        }

        return max(0, (int) now()->diffInSeconds($endsAt, false));
    }
}
