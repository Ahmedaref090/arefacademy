<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    protected $fillable = [
        'user_id', 'device_uuid', 'device_name', 'last_seen_ip', 'last_active_at',
    ];

    protected function casts(): array
    {
        return [
            'last_active_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Parsed OS platform from the stored device_name (raw user agent).
     */
    public function platform(): string
    {
        $ua = $this->device_name ?? '';

        return match (true) {
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Mac OS') => 'macOS',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown device',
        };
    }

    /**
     * Parsed browser from the stored device (raw user agent).
     */
    public function browser(): string
    {
        $ua = $this->device_name ?? '';

        return match (true) {
            str_contains($ua, 'Edg') => 'Edge',
            str_contains($ua, 'OPR') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Firefox') => 'Firefox',
            str_contains($ua, 'Safari') => 'Safari',
            default => 'Unknown browser',
        };
    }

    /**
     * Coarse device class derived from the user agent (mobile / tablet / desktop).
     */
    public function deviceType(): string
    {
        $ua = $this->device_name ?? '';

        return match (true) {
            str_contains($ua, 'iPad') => 'Tablet',
            str_contains($ua, 'Tablet') => 'Tablet',
            str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone') => 'Mobile',
            default => 'Desktop',
        };
    }
}
