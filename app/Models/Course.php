<?php

namespace App\Models;

use App\Enums\GradeLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'description', 'price', 'duration_weeks',
        'thumbnail', 'grade_level', 'is_published', 'whatsapp_group_link',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_published' => 'boolean',
            'grade_level' => GradeLevel::class,
        ];
    }

    /**
     * Courses are identified by slug in URLs (matches {course:slug} bindings).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Public URL for the course thumbnail, streamed from the private disk
     * via the courses.thumbnail route. Null when no thumbnail is set.
     */
    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail ? route('courses.thumbnail', $this) : null;
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at'])
            ->withTimestamps();
    }

    public function progressFor(User $user): int
    {
        $total = $this->lessons()->count();

        if ($total === 0) {
            return 0;
        }

        $completed = $this->lessons()
            ->whereHas('completedByUsers', fn ($q) => $q
                ->where('users.id', $user->id)
                ->whereNotNull('lesson_user.completed_at'))
            ->count();

        return (int) round(($completed / $total) * 100);
    }
}
