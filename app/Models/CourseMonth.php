<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseMonth extends Model
{
    use HasTranslations;

    /** Columns stored as translatable JSON objects ({ar, en}). */
    public array $translatable = ['name'];

    protected $fillable = [
        'course_id', 'name', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['name' => 'array'];
    }

    public function getNameAttribute(): string
    {
        return $this->getTranslation('name');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    /**
     * Students who requested to buy this month.
     * Pivot "status": pending | approved | rejected.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_month_user')
            ->withPivot('status')
            ->withTimestamps();
    }
}
