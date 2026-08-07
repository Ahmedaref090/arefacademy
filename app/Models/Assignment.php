<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasTranslations;

    /** Columns stored as translatable JSON objects ({ar, en}). */
    public array $translatable = ['title', 'description'];

    protected $fillable = [
        'lesson_id', 'title', 'description', 'max_score', 'deadline',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'array',
            'description' => 'array',
            'deadline' => 'datetime',
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

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function submissionFor(User $user): ?Submission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }
}
