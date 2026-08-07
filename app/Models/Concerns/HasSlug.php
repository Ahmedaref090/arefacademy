<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Auto-generates an SEO-friendly slug from a source column and keeps it
 * unique. Drop `use HasSlug;` into any model that has a `slug` column.
 *
 * Example:
 *   class Article extends Model {
 *       use HasSlug;
 *       protected $slugSource = 'title';    // column used to build the slug
 *   }
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug) && ! empty($model->{$model->slugSource()})) {
                $model->slug = $model->uniqueSlug($model->{$model->slugSource()});
            }

            if (empty($model->slug)) {
                $model->slug = Str::slug((string) $model->getKey() ?? now());
            }
        });
    }

    protected function slugSource(): string
    {
        return isset($this->slugSource) ? $this->slugSource : 'title';
    }

    /**
     * Append a numeric suffix until the slug no longer collides.
     */
    protected function uniqueSlug(string $value): string
    {
        $base = Str::slug($value) ?: 'item';

        // Skip count lookup for the simplest case (first hit, no suffix needed).
        $exists = static::query()
            ->where('slug', $base)
            ->when($this->getKey(), fn ($q) => $q->where('id', '!=', $this->getKey()))
            ->exists();

        if (! $exists) {
            return $base;
        }

        $i = 2;
        while (static::query()
            ->where('slug', "{$base}-{$i}")
            ->when($this->getKey(), fn ($q) => $q->where('id', '!=', $this->getKey()))
            ->exists()) {
            $i++;
        }

        return "{$base}-{$i}";
    }

    /**
     * URLs resolve against the slug instead of the numeric id, so any
     * route binding for this model automatically uses {article:slug}.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
