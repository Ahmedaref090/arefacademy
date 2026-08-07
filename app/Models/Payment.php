<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Payment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'course_month_id', 'enrollment_id',
        'amount', 'status', 'payment_method',
        'sender_details', 'receipt_image_path', 'rejection_reason',
        'paid_at', 'expires_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'reviewed_at' => 'datetime',
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

    /**
     * The specific month this payment is for (per-month courses only).
     * Null for full-course (lifetime) payments.
     *
     * @deprecated A single receipt may now cover multiple months; use courseMonths().
     */
    public function courseMonth(): BelongsTo
    {
        return $this->belongsTo(CourseMonth::class);
    }

    /**
     * Every course month covered by this single receipt. A receipt can be
     * consolidated across several months of a per-month course, but may also
     * contain zero months for a full-course (lifetime) payment.
     */
    public function courseMonths(): BelongsToMany
    {
        return $this->belongsToMany(CourseMonth::class, 'course_month_payment')
            ->orderBy('course_months.sort_order');
    }

    /**
     * The months this receipt covers, supporting both the new consolidated
     * pivot relationship and legacy single-month receipts (course_month_id).
     */
    public function displayMonths(): \Illuminate\Support\Collection
    {
        if ($this->courseMonths->isNotEmpty()) {
            return $this->courseMonths;
        }

        return $this->course_month_id ? collect([$this->courseMonth]) : collect();
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

    /**
     * Payments that have been reviewed (approved or rejected) — the history log.
     */
    public function scopeReviewed(Builder $query): Builder
    {
        return $query->whereIn('status', [PaymentStatus::Approved, PaymentStatus::Rejected]);
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
