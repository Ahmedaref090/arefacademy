<?php

namespace App\Models\Concerns;

trait HasTranslations
{
    /**
     * Columns that hold translatable JSON data ({locale: value}).
     * Override by setting `protected $translatable = ['title', ...];`.
     */
    public function translatableColumns(): array
    {
        return property_exists($this, 'translatable') ? $this->translatable : [];
    }

    /**
     * Resolve a translatable column for the current app locale, falling
     * back to the fallback locale, then to the first available translation.
     */
    public function getTranslation(string $key, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $value = $this->getAttributeFromArray($key);

        if (is_string($value) && $value !== '' && ($value[0] === '{' || $value[0] === '[')) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            }
        }

        if (is_array($value)) {
            return (string) ($value[$locale]
                ?? $value[config('app.fallback_locale', 'en')]
                ?? reset($value)
                ?? '');
        }

        return (string) ($value ?? '');
    }

    /**
     * Store one locale's value for a translatable column, preserving the
     * other locales' values.
     */
    public function setTranslation(string $key, string $locale, $value): static
    {
        $translations = $this->rawTranslations($key);
        $translations[$locale] = (string) $value;
        $this->{$key} = $translations;

        return $this;
    }

    /**
     * The raw {locale: value} array for a translatable column.
     */
    public function rawTranslations(string $key): array
    {
        $value = $this->getAttributeFromArray($key);

        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) && $value !== '' && ($value[0] === '{' || $value[0] === '[')) {
            $decoded = json_decode($value, true);

            return is_array($decoded) ? $decoded : [];
        }

        return $value === null || $value === '' ? [] : ['ar' => (string) $value];
    }

    /**
     * Apply flat "<column>_ar"/"<column>_en" request data to the model.
     */
    public function fillTranslations(array $data): static
    {
        foreach ($this->translatableColumns() as $column) {
            foreach (['ar', 'en'] as $locale) {
                $field = "{$column}_{$locale}";

                if (array_key_exists($field, $data)) {
                    $this->setTranslation($column, $locale, $data[$field] ?? '');
                }
            }
        }

        return $this;
    }
}
