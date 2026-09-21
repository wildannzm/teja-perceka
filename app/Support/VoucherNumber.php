<?php

namespace App\Support;

use App\Models\JournalEntry;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for voucher number sequencing.
 *
 * Scope: prefix + YYYY-MM(date) + business_unit_id.
 * Insert-and-shift: a new voucher takes its chronological slot by date,
 * later vouchers shift by +N so date and number order always agree.
 */
class VoucherNumber
{
    /**
     * @return array{prefix: string, number: string, sequence: int}
     */
    public static function next(string $prefix, string $date, ?int $tourismUnitId = null): array
    {
        $number = self::nextBatch($prefix, $date, 1, $tourismUnitId)[0];

        return [
            'prefix' => $prefix,
            'number' => $number,
            'sequence' => (int) substr($number, -3),
        ];
    }

    /**
     * Reserve $count sequential numbers at the chronological position.
     *
     * @return string[]
     */
    public static function nextBatch(string $prefix, string $date, int $count, ?int $tourismUnitId = null): array
    {
        return DB::transaction(function () use ($prefix, $date, $count, $tourismUnitId) {
            $day = Carbon::parse($date)->format('Y-m-d');

            $query = JournalEntry::where('voucher_number', 'like', $prefix.'%')
                ->whereDate('transaction_date', '>=', substr($day, 0, 7).'-01')
                ->whereDate('transaction_date', '<', Carbon::parse($day)->startOfMonth()->addMonth()->format('Y-m-d'))
                ->lockForUpdate();

            if ($tourismUnitId !== null) {
                $query->where('business_unit_id', $tourismUnitId);
            }

            // One voucher = one nomor_bukti (possibly many debit/credit rows), ordered chronologically.
            $vouchers = [];
            foreach ((clone $query)->orderBy('transaction_date')->orderBy('id')->get(['id', 'voucher_number', 'transaction_date', 'daily_transaction_id']) as $row) {
                $key = $row->voucher_number;
                $vouchers[$key]['date'] ??= Carbon::parse($row->transaction_date)->format('Y-m-d');
                $vouchers[$key]['ids'][] = $row->id;
                if ($row->daily_transaction_id !== null) {
                    $vouchers[$key]['dailies'][$row->daily_transaction_id] = true;
                }
            }

            // Never silently grow a corruption: one voucher shared by several
            // daily transactions means numbering already broke (repair with
            // `voucher:repair-duplicates` instead of reusing the number).
            foreach ($vouchers as $number => $voucher) {
                if (count($voucher['dailies'] ?? []) > 1) {
                    throw new \RuntimeException(
                        "Duplicate voucher {$number} shared by daily transactions "
                        .implode(',', array_keys($voucher['dailies']))
                        .'. Run `php artisan voucher:repair-duplicates` first.'
                    );
                }
            }
            $ordered = array_values($vouchers);

            // Base offset: if BUMDes has prior cash balance, reserve DBM001 for Saldo Kas
            $baseOffset = 0;
            if ($prefix === 'DBM') {
                $monthStart = substr($day, 0, 7).'-01';
                if (SaldoKasBumdes::getOpeningBalance($monthStart) > 0) {
                    $baseOffset = 1;
                }
            }

            // Insertion rank: after the last voucher with an older date.
            $k = 0;
            foreach ($ordered as $voucher) {
                if ($voucher['date'] < $day) {
                    $k++;
                } else {
                    break;
                }
            }

            // Shift backwards (largest first) to avoid name collisions.
            for ($i = count($ordered) - 1; $i >= $k; $i--) {
                $old = (int) substr(array_keys($vouchers)[$i], -3);
                JournalEntry::whereIn('id', $ordered[$i]['ids'])
                    ->update(['voucher_number' => $prefix.str_pad($old + $count, 3, '0', STR_PAD_LEFT)]);
            }

            $numbers = [];
            for ($i = 1; $i <= $count; $i++) {
                $numbers[] = $prefix.str_pad($baseOffset + $k + $i, 3, '0', STR_PAD_LEFT);
            }

            return $numbers;
        });
    }
}
