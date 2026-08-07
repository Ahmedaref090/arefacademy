<?php

namespace App\Translators;

use Illuminate\Support\Facades\Log;
use Illuminate\Translation\Translator;

/**
 * Translator subclass that flags missing translation keys in local/dev.
 *
 * When a key has no translation for the active locale, Laravel silently
 * returns the key itself (or the fallback locale's value). This decorator
 * logs a warning so untranslated strings surface instantly during
 * development instead of shipping silently.
 *
 * Identity mappings (e.g. en.json "Login" => "Login") must not be treated
 * as missing, so existence is checked against the loaded JSON lines rather
 * than by comparing the resolved value to the key.
 */
class MissingKeyLoggerTranslator extends Translator
{
    /**
     * Get the translation for the given key, logging any missing lines.
     *
     * @return mixed
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        $locale = $locale ?: $this->locale;

        $translation = parent::get($key, $replace, $locale, $fallback);

        if ($translation === $key && ! $this->existsInLoadedLines($key, $locale)) {
            Log::debug("Missing translation key [{$key}] for locale [{$locale}]");
        }

        return $translation;
    }

    /**
     * Whether the key exists in the JSON lines loaded for the current
     * or the fallback locale (parent::get already loaded both).
     */
    protected function existsInLoadedLines(string $key, string $locale): bool
    {
        foreach (array_unique([$locale, ...$this->localeArray($this->fallback)]) as $loc) {
            foreach (['*', ''] as $namespace) {
                if (isset($this->loaded[$namespace]['*'][$loc]) && array_key_exists($key, $this->loaded[$namespace]['*'][$loc])) {
                    return true;
                }
            }
        }

        return false;
    }
}
