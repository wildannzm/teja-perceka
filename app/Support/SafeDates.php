<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class SafeDates
{
    public static function day(?string $value, string $fallbackFormat = 'Y-m-d'): Carbon
    {
        try {
            $date = Carbon::parse($value ?: Carbon::today()->format($fallbackFormat));

            return $date->isValid() ? $date : Carbon::today();
        } catch (\Throwable) {
            return Carbon::today();
        }
    }

    public static function month(?string $value): Carbon
    {
        $value = is_string($value) && preg_match('/^\d{4}-\d{2}$/', $value) ? $value : Carbon::now()->format('Y-m');

        try {
            return Carbon::parse($value.'-01');
        } catch (\Throwable) {
            return Carbon::now()->startOfMonth();
        }
    }

    public static function year(?string $value): int
    {
        $year = is_numeric($value) ? (int) $value : (int) Carbon::now()->format('Y');

        return max(2000, min(2100, $year));
    }
}
