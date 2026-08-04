<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'title', 'description', 'video_url', 'video_path',
        'duration_minutes', 'sort_order', 'is_free',
    ];

    protected function casts(): array
    {
        return [
            'is_free' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function completedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'lesson_user')
            ->withPivot(['watch_seconds', 'completed_at'])
            ->withTimestamps();
    }

    /**
     * Convert a YouTube/Vimeo watch URL into an embeddable URL.
     * Returns null when video_url is empty or not a known provider.
     */
    public function embedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([\w-]{6,})/', $this->video_url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return null;
    }

    /**
     * Resolve the playable URL for a self-hosted video, whatever format
     * the admin typed into the video_path field:
     *
     *  - Full URL ("https://…")            → used as-is
     *  - "/storage/…" or "storage/…"       → already a public URL path
     *  - "courses/videos/intro.mp4"        → public disk → /storage/courses/videos/intro.mp4
     */
    public function videoSrc(): ?string
    {
        if (! $this->video_path) {
            return null;
        }

        if (Str::startsWith($this->video_path, ['http://', 'https://', '//'])) {
            return $this->video_path;
        }

        if (Str::startsWith($this->video_path, ['/storage/', 'storage/'])) {
            return Str::start($this->video_path, '/');
        }

        return Storage::disk('public')->url($this->video_path);
    }
}
