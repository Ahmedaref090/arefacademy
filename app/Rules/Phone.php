<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Phone implements ValidationRule
{
    // Standard Egyptian mobile format: 01[0125] + 8 digits = exactly 11 digits.
    public const PATTERN = '/^01[0125][0-9]{8}$/';

    /**
     * Validate that the value is a strictly-numeric, exactly-11-digit
     * Egyptian mobile number (e.g. 01012345678). Letters, spaces and
     * special characters are rejected.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $normalized = is_string($value) ? preg_replace('/[^0-9]/', '', $value) : '';

        if ($normalized !== $value || preg_match(self::PATTERN, $normalized) !== 1) {
            $fail(__('Invalid phone number. It must be strictly 11 digits and contain numbers only.'));
        }
    }
}
