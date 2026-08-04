<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    /**
     * Login records are immutable — only created_at is managed.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'session_id', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Best-effort browser detection from the raw user agent. */
    public function browser(): string
    {
        $ua = $this->user_agent ?? '';

        return match (true) {
            str_contains($ua, 'Edg') => 'Edge',
            str_contains($ua, 'OPR') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari') => 'Safari',
            default => 'Unknown browser',
        };
    }

    /** Best-effort platform detection from the raw user agent. */
    public function platform(): string
    {
        $ua = $this->user_agent ?? '';

        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown device',
        };
    }

    public function isCurrentSession(): bool
    {
        return $this->session_id !== null && $this->session_id === session()->getId();
    }
}
