<?php

namespace App\Support;

class TextName
{
    /**
     * Names must contain letters (Myanmar/Latin) and must not contain digits.
     */
    public static function isValid(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $trimmed = trim($value);
        if ($trimmed === '' || mb_strlen($trimmed) < 2) {
            return false;
        }

        // Reject Latin / Myanmar digits anywhere
        if (preg_match('/[0-9၀-၉]/u', $trimmed)) {
            return false;
        }

        // Require at least one letter character
        return (bool) preg_match('/\p{L}/u', $trimmed);
    }

    public static function validationRule(string $label = 'အမည်'): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail) use ($label): void {
            if (! is_string($value) || ! self::isValid($value)) {
                $fail("{$label}တွင် စကားလုံးသာ ဖြည့်ရမည် — ဂဏန်း ထည့်၍မရပါ။");
            }
        };
    }
}
