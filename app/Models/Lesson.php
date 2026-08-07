<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
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
    use HasTranslations;

    /** Columns stored as translatable JSON objects ({ar, en}). */
    public array $translatable = ['title', 'description'];

    protected static function booted(): void
    {
        static::deleting(function (Lesson $lesson) {
            // Remove the video from Cloudflare R2 when the lesson is deleted so
            // no orphaned files remain in the bucket.
            if ($lesson->isStoredOnR2() && Storage::disk('r2')->exists($lesson->video_path)) {
                Storage::disk('r2')->delete($lesson->video_path);
            }
        });
    }

    protected $fillable = [
        'course_id', 'course_month_id', 'title', 'description', 'video_url', 'video_path',
        'duration_minutes', 'sort_order', 'is_free',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'is_free' => 'boolean',
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

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * The calendar month this lesson belongs to (per-month courses only).
     * Null for lessons in lifetime courses.
     */
    public function month(): BelongsTo
    {
        return $this->belongsTo(CourseMonth::class, 'course_month_id');
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
            return 'https://www.youtube.com/embed/'.$m[1];
        }

        if (preg_match('/vimeo\.com\/(\d+)/', $this->video_url, $m)) {
            return 'https://player.vimeo.com/video/'.$m[1];
        }

        return null;
    }

    /**
     * Resolve the playable URL for a self-hosted video, whatever format
     * the admin typed into the video_path field:
     *
     *  - Full URL ("https://…")          → used as-is
     *  - "/storage/…" or "storage/…"     → already a public URL path
     *  - "videos/v1.mp4"                 → public disk file, verified to exist,
     *                                      served at /storage/videos/v1.mp4
     *  - an R2 object key ("videos/…")    → a temporary, signed R2 URL
     *
     * Returns null when video_path is empty or the file is missing.
     */
    public function videoSrc(): ?string
    {
        if (! $this->video_path) {
            return null;
        }

        // R2-stored video — return a signed, expiring URL.
        if ($this->isStoredOnR2()) {
            return $this->tempVideoUrl();
        }

        // Full URL (e.g. "https://cdn.example.com/v1.mp4") — use as-is.
        if (Str::startsWith($this->video_path, ['http://', 'https://', '//'])) {
            return $this->video_path;
        }

        // Already a public URL path ("/storage/…" or "storage/…").
        if (Str::startsWith($this->video_path, ['/storage/', 'storage/'])) {
            return asset(Str::start($this->video_path, '/'));
        }

        // Relative path on the public disk — e.g. "videos/v1.mp4" physically
        // at storage/app/public/videos/v1.mp4, publicly reachable through the
        // storage symlink at /storage/videos/v1.mp4.
        if (Storage::disk('public')->exists($this->video_path)) {
            return asset('storage/'.ltrim($this->video_path, '/'));
        }

        return null;
    }

    /**
     * True when the lesson's video lives on Cloudflare R2.
     */
    public function isStoredOnR2(): bool
    {
        if (! $this->video_path) {
            return false;
        }

        if (Str::startsWith($this->video_path, ['http://', 'https://', '/storage/', 'storage/'])) {
            return false;
        }

        return Storage::disk('r2')->exists($this->video_path);
    }

    /**
     * Temporary (3-hour) signed URL so a student can stream the video from
     * the private bucket. Returns null when the file is missing.
     */
    public function tempVideoUrl(): ?string
    {
        if (! $this->isStoredOnR2()) {
            return null;
        }

        return Storage::disk('r2')->temporaryUrl($this->video_path, now()->addHours(3));
    }
}
