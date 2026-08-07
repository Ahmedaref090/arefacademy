<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class FullName implements ValidationRule
{
    /**
     * At least three separate names (e.g. first + middle + last), each made
     * of Arabic or English letters. Allows for the multiple spaces someone
     * may type when auto-completing their name.
     */
    public const PATTERN = '/^[\p{L}]{2,}(?:\s+[\p{L}]{2,}){2,}$/u';

    /**
     * Validate that the value contains at least three distinct words made
     * up solely of Arabic or English letters.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = is_string($value) ? preg_replace('/\s+/u', ' ', trim($value)) : '';

        if (preg_match(self::PATTERN, $normalized) !== 1) {
            $fail(__('Please enter at least three names - Arabic or English characters'));
        }
    }
}