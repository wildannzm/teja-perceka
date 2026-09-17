<?php

namespace App\Support;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class SaldoKasBumdes
{
    /**
     * Calculate BUMDes opening cash balance prior to the given start date.
     * Opening Balance = (Total Prior Net Unit Income + Manual BUMDes Income) - Prior BUMDes Expenses (KBM).
     */
    public static function getOpeningBalance(Carbon|string $startDate): float
    {
        $day = Carbon::parse($startDate)->startOfDay()->format('Y-m-d');

        $dailyIncomeTotal = (float) TransaksiHarian::whereDate('tanggal', '<', $day)->sum('total_pemasukan');
        $dailyExpenseTotal = (float) TransaksiHarian::whereDate('tanggal', '<', $day)->sum('total_pengeluaran');
        $dailyNetUnitIncome = max(0.0, $dailyIncomeTotal - $dailyExpenseTotal);

        $journalIncomeTotal = (float) JurnalUmum::whereNotNull('unit_wisata_id')
            ->where('nomor_bukti', 'like', 'D%')
            ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
            ->whereDate('tanggal', '<', $day)
            ->sum('debet');
        $journalExpenseTotal = (float) JurnalUmum::whereNotNull('unit_wisata_id')
            ->where('nomor_bukti', 'like', 'K%')
            ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
            ->whereDate('tanggal', '<', $day)
            ->sum('kredit');
        $journalNetUnitIncome = max(0.0, $journalIncomeTotal - $journalExpenseTotal);

        $totalNetUnitIncome = max($dailyNetUnitIncome, $journalNetUnitIncome);

        $manualBumdesIncome = (float) JurnalUmum::where('nomor_bukti', 'like', 'DBM%')
            ->whereNull('transaksi_harian_id')
            ->whereNull('unit_wisata_id')
            ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
            ->whereDate('tanggal', '<', $day)
            ->sum('debet');

        $bumdesExpenses = (float) JurnalUmum::where('nomor_bukti', 'like', 'KBM%')
            ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
            ->whereDate('tanggal', '<', $day)
            ->sum('kredit');

        $openingBalance = ($totalNetUnitIncome + $manualBumdesIncome) - $bumdesExpenses;

        return max(0.0, $openingBalance);
    }

    /**
     * Calculate net revenue per business unit for the specified date range (revenue minus expenses).
     *
     * @return Collection<int, array{unit: UnitWisata, amount: float}>
     */
    public static function getNetIncomePerUnit(Carbon|string $startDate, Carbon|string $endDate): Collection
    {
        $startDay = Carbon::parse($startDate)->startOfDay()->format('Y-m-d');
        $endDay = Carbon::parse($endDate)->endOfDay()->format('Y-m-d');

        $units = UnitWisata::orderBy('id')->get();
        $results = collect();

        foreach ($units as $unit) {
            $dailyIncomeTotal = (float) TransaksiHarian::where('unit_wisata_id', $unit->id)
                ->whereDate('tanggal', '>=', $startDay)
                ->whereDate('tanggal', '<=', $endDay)
                ->sum('total_pemasukan');

            $dailyExpenseTotal = (float) TransaksiHarian::where('unit_wisata_id', $unit->id)
                ->whereDate('tanggal', '>=', $startDay)
                ->whereDate('tanggal', '<=', $endDay)
                ->sum('total_pengeluaran');

            $journalIncomeTotal = (float) JurnalUmum::where('unit_wisata_id', $unit->id)
                ->whereDate('tanggal', '>=', $startDay)
                ->whereDate('tanggal', '<=', $endDay)
                ->where('nomor_bukti', 'like', 'D%')
                ->where('debet', '>', 0)
                ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
                ->sum('debet');

            $journalExpenseTotal = (float) JurnalUmum::where('unit_wisata_id', $unit->id)
                ->whereDate('tanggal', '>=', $startDay)
                ->whereDate('tanggal', '<=', $endDay)
                ->where('nomor_bukti', 'like', 'K%')
                ->where('kredit', '>', 0)
                ->whereHas('kodeAkun', fn ($query) => $query->whereIn('kode', ['1-1100', '1-1200']))
                ->sum('kredit');

            $totalIncome = max($dailyIncomeTotal, $journalIncomeTotal);
            $totalExpense = max($dailyExpenseTotal, $journalExpenseTotal);

            // Net unit revenue = gross income - unit expenses
            $netAmount = max(0.0, $totalIncome - $totalExpense);

            if ($netAmount > 0) {
                $results->push([
                    'unit' => $unit,
                    'amount' => $netAmount,
                ]);
            }
        }

        return $results;
    }

    /**
     * Get aggregate net unit income across all units within the specified period.
     */
    public static function getTotalNetUnitIncome(Carbon|string $startDate, Carbon|string $endDate): float
    {
        return (float) self::getNetIncomePerUnit($startDate, $endDate)->sum('amount');
    }

    /**
     * Generate 2 virtual opening balance journal lines (DBM001).
     *
     * @return Collection<int, JurnalUmum>
     */
    public static function makeOpeningBalanceEntries(Carbon|string $startDate, float $amount, string $mode = 'bulanan'): Collection
    {
        if ($amount <= 0) {
            return collect();
        }

        $date = Carbon::parse($startDate)->startOfDay();
        $description = match ($mode) {
            'tahunan' => 'Saldo Kas Tahun '.$date->copy()->subYear()->format('Y'),
            'semester' => 'Saldo Kas '.($date->month <= 6 ? 'Semester 2 '.$date->copy()->subYear()->format('Y') : 'Semester 1 '.$date->format('Y')),
            default => 'Saldo Kas '.$date->copy()->subMonth()->translatedFormat('F'),
        };

        $cashAccount = KodeAkun::where('kode', '1-1100')->first();
        $revenueAccount = KodeAkun::where('kode', '4-2000')->first();

        if (! $cashAccount || ! $revenueAccount) {
            return collect();
        }

        $cashEntry = new JurnalUmum([
            'nomor_bukti' => 'DBM001',
            'tanggal' => $date,
            'keterangan' => $description,
            'kode_akun_id' => $cashAccount->id,
            'debet' => $amount,
            'kredit' => 0,
            'unit_wisata_id' => null,
        ]);
        $cashEntry->setRelation('kodeAkun', $cashAccount);
        $cashEntry->tanggal = $date;

        $revenueEntry = new JurnalUmum([
            'nomor_bukti' => 'DBM001',
            'tanggal' => $date,
            'keterangan' => $description,
            'kode_akun_id' => $revenueAccount->id,
            'debet' => 0,
            'kredit' => $amount,
            'unit_wisata_id' => null,
        ]);
        $revenueEntry->setRelation('kodeAkun', $revenueAccount);
        $revenueEntry->tanggal = $date;

        return collect([$cashEntry, $revenueEntry]);
    }

    /**
     * Generate virtual journal entries summarizing net revenue per business unit at the end of the period (DBM002, DBM003, etc.).
     *
     * @return Collection<int, JurnalUmum>
     */
    public static function makeUnitRevenueEntries(Carbon|string $startDate, Carbon|string $endDate, int $startVoucherNumber = 2): Collection
    {
        $unitIncomes = self::getNetIncomePerUnit($startDate, $endDate);
        if ($unitIncomes->isEmpty()) {
            return collect();
        }

        $cashAccount = KodeAkun::where('kode', '1-1100')->first();
        $revenueAccount = KodeAkun::where('kode', '4-2000')->first();

        if (! $cashAccount || ! $revenueAccount) {
            return collect();
        }

        $entries = collect();
        $date = Carbon::parse($endDate)->endOfDay();
        $voucherCounter = $startVoucherNumber;

        foreach ($unitIncomes as $item) {
            $unit = $item['unit'];
            $amount = $item['amount'];
            $voucherNumber = sprintf('DBM%03d', $voucherCounter++);
            $description = 'Pendapatan '.$unit->nama;

            $cashEntry = new JurnalUmum([
                'nomor_bukti' => $voucherNumber,
                'tanggal' => $date,
                'keterangan' => $description,
                'kode_akun_id' => $cashAccount->id,
                'debet' => $amount,
                'kredit' => 0,
                'unit_wisata_id' => $unit->id,
            ]);
            $cashEntry->setRelation('kodeAkun', $cashAccount);
            $cashEntry->setRelation('unitWisata', $unit);
            $cashEntry->tanggal = $date;

            $revenueEntry = new JurnalUmum([
                'nomor_bukti' => $voucherNumber,
                'tanggal' => $date,
                'keterangan' => $description,
                'kode_akun_id' => $revenueAccount->id,
                'debet' => 0,
                'kredit' => $amount,
                'unit_wisata_id' => $unit->id,
            ]);
            $revenueEntry->setRelation('kodeAkun', $revenueAccount);
            $revenueEntry->setRelation('unitWisata', $unit);
            $revenueEntry->tanggal = $date;

            $entries->push($cashEntry, $revenueEntry);
        }

        return $entries;
    }

    /**
     * Retrieve all virtual BUMDes journal entries (opening balance + unit revenue summaries).
     *
     * @return Collection<int, JurnalUmum>
     */
    public static function getBumdesVirtualEntries(Carbon|string $startDate, Carbon|string $endDate, string $mode = 'bulanan'): Collection
    {
        $entries = collect();

        $startVoucherNumber = 1;
        // Opening balance is only applicable to periodic reporting (monthly, semester, annual)
        if (in_array($mode, ['bulanan', 'semester', 'tahunan'], true)) {
            $openingBalance = self::getOpeningBalance($startDate);
            if ($openingBalance > 0) {
                $entries = $entries->concat(self::makeOpeningBalanceEntries($startDate, $openingBalance, $mode));
                $startVoucherNumber = 2;
            }
        }

        $unitRevenueEntries = self::makeUnitRevenueEntries($startDate, $endDate, $startVoucherNumber);
        $entries = $entries->concat($unitRevenueEntries);

        return $entries;
    }

    /**
     * Merge real BUMDes journal transactions with virtual entries and sort them chronologically.
     */
    public static function attachToTransactions(
        Collection $transactions,
        Carbon|string $startDate,
        Carbon|string $endDate,
        string $mode = 'bulanan',
        string $sortField = 'tanggal',
        string $sortDirection = 'asc'
    ): Collection {
        $virtualEntries = self::getBumdesVirtualEntries($startDate, $endDate, $mode);
        if ($virtualEntries->isEmpty()) {
            return $transactions;
        }

        $allTransactions = $transactions->concat($virtualEntries);
        $isDescending = strtolower($sortDirection) === 'desc';

        return $allTransactions->sort(function ($first, $second) use ($sortField, $isDescending) {
            if ($sortField === 'nomor_bukti') {
                $comparison = strcmp($first->nomor_bukti, $second->nomor_bukti);
                if ($comparison !== 0) {
                    return $isDescending ? -$comparison : $comparison;
                }
            }

            $firstTimestamp = Carbon::parse($first->tanggal)->timestamp;
            $secondTimestamp = Carbon::parse($second->tanggal)->timestamp;
            if ($firstTimestamp !== $secondTimestamp) {
                return $isDescending ? ($secondTimestamp <=> $firstTimestamp) : ($firstTimestamp <=> $secondTimestamp);
            }

            $voucherComparison = strcmp($first->nomor_bukti, $second->nomor_bukti);
            if ($voucherComparison !== 0) {
                return $isDescending ? -$voucherComparison : $voucherComparison;
            }

            return $isDescending ? (($second->id ?? 0) <=> ($first->id ?? 0)) : (($first->id ?? 0) <=> ($second->id ?? 0));
        })->values();
    }

    // ── Backward Compatibility Aliases ─────────────────────────────────────

    public static function getSaldoAwal(Carbon|string $startDate): float
    {
        return self::getOpeningBalance($startDate);
    }

    public static function getPendapatanPerUnit(Carbon|string $startDate, Carbon|string $endDate): Collection
    {
        return self::getNetIncomePerUnit($startDate, $endDate);
    }

    public static function getTotalPendapatanUnit(Carbon|string $startDate, Carbon|string $endDate): float
    {
        return self::getTotalNetUnitIncome($startDate, $endDate);
    }

    public static function makeSaldoAwalEntries(Carbon|string $startDate, float $amount, string $mode = 'bulanan'): Collection
    {
        return self::makeOpeningBalanceEntries($startDate, $amount, $mode);
    }
}
