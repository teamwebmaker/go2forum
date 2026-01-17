<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalizeGe(string $input): string
    {
        $digits = preg_replace('/\D+/', '', $input) ?? '';
        $digits = trim($digits);

        if (str_starts_with($digits, '00995')) {
            $digits = substr($digits, 5);
        } elseif (str_starts_with($digits, '995')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0') && strlen($digits) === 10) {
            $digits = substr($digits, 1);
        }

        return $digits;
    }

    public static function validateGe(string $digits): void
    {
        if (!preg_match('/^5\d{8}$/', $digits)) {
            throw new \InvalidArgumentException('Phone must be Georgian mobile (5xxxxxxxx).');
        }
    }
}
