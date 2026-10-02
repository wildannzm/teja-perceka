<?php

namespace App\Support;

class Rupiah
{
    /**
     * Parse a dotted id-ID rupiah value ("15.000") into a float.
     * Always strip thousand separators first: (float) "15.000" is 15 (wrong).
     */
    public static function parse(mixed $value): float
    {
        return (float) preg_replace('/\D/', '', (string) ($value ?? ''));
    }

    /**
     * Format a number for id-ID display ("15000" -> "15.000").
     */
    public static function format(float|int|string|null $value): string
    {
        return number_format((float) ($value ?? 0), 0, ',', '.');
    }
}
