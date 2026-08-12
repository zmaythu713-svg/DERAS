<?php

namespace App\Support;

/**
 * Display helpers for form inputs.
 * Empty / zero numeric values stay blank so placeholder="0" can show.
 */
class FormValue
{
    public static function number(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_numeric($value) && (float) $value == 0.0) {
            return '';
        }

        return (string) $value;
    }
}
