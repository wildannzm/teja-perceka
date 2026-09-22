<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PdfExport
{
    /**
     * Build a clean Indonesian filename for PDF exports.
     *
     * Words are separated by spaces only — never underscores.
     * Each part is trimmed and collapsed to single spaces.
     *
     * Example: "Jurnal Umum BUMDes Ringkas September 2026.pdf"
     */
    public static function filename(string ...$parts): string
    {
        $cleaned = collect($parts)
            ->map(fn ($part) => (string) preg_replace('/["\'\r\n;\\\\]/', '', (string) $part))
            ->map(fn ($part) => trim((string) preg_replace('/[\s_\/-]+/', ' ', $part)))
            ->map(fn ($part) => (string) preg_replace('/\s+/', ' ', $part))
            ->filter()
            ->implode(' ');

        return Str::limit(Str::ascii($cleaned), 180, '').'.pdf';
    }

    /**
     * Signature date shown above the signatory: the last day of the
     * reporting period, e.g. "30 September 2026".
     */
    public static function signatureDate(Carbon $end): string
    {
        return $end->translatedFormat('d F Y');
    }
}
