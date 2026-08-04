<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'status', 'enrolled_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', EnrollmentStatus::Active);
    }

    /**
     * An enrollment is expired when it has an expires_at date in the past.
     * expires_at = null means lifetime access (never expires).
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Active status AND not past the expiry date. */
    public function isActive(): bool
    {
        return $this->status === EnrollmentStatus::Active && ! $this->isExpired();
    }

    public function activate(): void
    {
        $this->update([
            'status' => EnrollmentStatus::Active,
            'enrolled_at' => now(),
            // Monthly subscription: 30 days of access from activation.
            'expires_at' => now()->addDays(30),
        ]);
    }
}
