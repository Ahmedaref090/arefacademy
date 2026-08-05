<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'enrollment_id',
        'amount', 'status', 'payment_method',
        'sender_details', 'receipt_image_path', 'rejection_reason',
        'paid_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
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

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Pending);
    }

    /**
     * Backward-compatible alias for scopeApproved() — existing code
     * (e.g. admin dashboard revenue stats) refers to approved payments as "paid".
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', PaymentStatus::Approved);
    }

    public function isApproved(): bool
    {
        return $this->status === PaymentStatus::Approved;
    }

    public function isPending(): bool
    {
        return $this->status === PaymentStatus::Pending;
    }

    public function isRejected(): bool
    {
        return $this->status === PaymentStatus::Rejected;
    }

    /** Backward-compatible alias for isApproved(). */
    public function isPaid(): bool
    {
        return $this->isApproved();
    }
}
