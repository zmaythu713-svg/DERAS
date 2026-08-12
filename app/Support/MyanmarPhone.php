<?php

namespace App\Support;

class MyanmarPhone
{
    private const MYANMAR_DIGITS = ['၀', '၁', '၂', '၃', '၄', '၅', '၆', '၇', '၈', '၉'];
    private const LATIN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /** Digits after the 09 / ၀၉ prefix. */
    public const LOCAL_DIGITS = 9;

    /**
     * Normalize to Latin digits starting with 09 (e.g. 09450708675).
     * Accepts: 09…, ၀၉…, 9… (national), 959… / +959…
     * Does NOT invent a 09 prefix for unrelated digit strings.
     */
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '') {
            return null;
        }

        $digits = self::toLatinDigits($trimmed);
        $digits = preg_replace('/\D+/', '', $digits) ?? '';

        if ($digits === '') {
            return null;
        }

        // +959 / 959… → 09…
        if (str_starts_with($digits, '959')) {
            $digits = '0' . substr($digits, 2);
        }

        // National format without leading 0: 9XXXXXXXXX → 09XXXXXXXXX
        if (str_starts_with($digits, '9') && ! str_starts_with($digits, '09')) {
            $digits = '0' . $digits;
        }

        if (! str_starts_with($digits, '09')) {
            return $digits;
        }

        // Keep at most 09 + 9 local digits
        if (strlen($digits) > 2 + self::LOCAL_DIGITS) {
            $digits = substr($digits, 0, 2 + self::LOCAL_DIGITS);
        }

        return $digits;
    }

    /**
     * Display format: 09-XXXXXXXXX
     */
    public static function format(?string $phone): string
    {
        $normalized = self::normalize($phone);
        if ($normalized === null || ! preg_match('/^09\d*$/', $normalized)) {
            return is_string($phone) ? trim($phone) : '';
        }

        $rest = substr($normalized, 2);

        return $rest === '' ? '09' : '09-' . $rest;
    }

    public static function isValid(?string $phone): bool
    {
        $normalized = self::normalize($phone);
        if ($normalized === null) {
            return false;
        }

        return (bool) preg_match('/^09\d{' . self::LOCAL_DIGITS . '}$/', $normalized);
    }

    public static function validationRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if (! self::isValid(is_string($value) ? $value : null)) {
                $fail('ဖုန်းနံပါတ်သည် 09 (သို့) ၀၉ ဖြင့် စပြီး ဂဏန်း ၉ လုံး ပြည့်အောင် ဖြည့်ရမည်။ ဥပမာ — 09-450708675');
            }
        };
    }

    private static function toLatinDigits(string $value): string
    {
        return str_replace(self::MYANMAR_DIGITS, self::LATIN_DIGITS, $value);
    }
}
