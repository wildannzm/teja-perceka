<?php

namespace App\Console\Commands;

use App\Models\JournalEntry;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[Signature('voucher:repair-duplicates {--dry-run : Show planned renames without applying changes} {--prefix= : Only repair this voucher prefix (e.g. DSB)} {--unit= : Only repair this business_unit_id} {--month= : Only repair this month (YYYY-MM)}')]
#[Description('Renumber voucher numbers shared by multiple daily transactions (same prefix+month+unit) chronologically')]
class RepairDuplicateVouchers extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $scopes = $this->findCorruptScopes();

        if ($scopes->isEmpty()) {
            $this->info('No duplicate vouchers found.');

            return self::SUCCESS;
        }

        foreach ($scopes as $scope) {
            $this->repairScope($scope);
        }

        return self::SUCCESS;
    }

    /**
     * Scopes (prefix + month + unit) where one voucher number is shared
     * by more than one daily transaction.
     *
     * @return Collection<int, array{prefix: string, month: string, unit: ?int}>
     */
    private function findCorruptScopes()
    {
        $groups = JournalEntry::query()
            ->selectRaw('voucher_number, business_unit_id, SUBSTR(transaction_date, 1, 7) as ym, COUNT(DISTINCT daily_transaction_id) as dailies')
            ->groupBy('voucher_number', 'business_unit_id', 'ym')
            ->havingRaw('COUNT(DISTINCT daily_transaction_id) > 1')
            ->get();

        return $groups
            ->map(function ($row) {
                if (! preg_match('/^([A-Z]+)(\d+)$/', (string) $row->voucher_number, $m)) {
                    return null;
                }

                return ['prefix' => $m[1], 'month' => (string) $row->ym, 'unit' => $row->business_unit_id];
            })
            ->filter()
            ->unique(fn ($scope) => $scope['prefix'].'|'.$scope['month'].'|'.($scope['unit'] ?? 'null'))
            ->filter(function ($scope) {
                if ($this->option('prefix') && $scope['prefix'] !== $this->option('prefix')) {
                    return false;
                }
                if ($this->option('unit') && (int) $scope['unit'] !== (int) $this->option('unit')) {
                    return false;
                }
                if ($this->option('month') && $scope['month'] !== $this->option('month')) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    /**
     * @param  array{prefix: string, month: string, unit: ?int}  $scope
     */
    private function repairScope(array $scope): void
    {
        $prefix = $scope['prefix'];
        $monthStart = $scope['month'].'-01';
        $monthEnd = Carbon::parse($monthStart)->endOfMonth()->format('Y-m-d');

        $rows = JournalEntry::query()
            ->where('voucher_number', 'like', $prefix.'%')
            ->whereDate('transaction_date', '>=', $monthStart)
            ->whereDate('transaction_date', '<=', $monthEnd)
            ->when($scope['unit'] !== null,
                fn ($query) => $query->where('business_unit_id', $scope['unit']),
                fn ($query) => $query->whereNull('business_unit_id'))
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get(['id', 'voucher_number', 'transaction_date', 'daily_transaction_id']);

        if ($rows->whereNull('daily_transaction_id')->isNotEmpty()) {
            $this->error("Scope {$prefix} {$scope['month']} unit ".($scope['unit'] ?? 'null').' contains manual rows without daily_transaction_id — repair manually.');

            return;
        }

        // One logical voucher = one (voucher number, daily transaction) pair.
        // An expense daily may own several vouchers, so renumber per pair —
        // never merge a daily's vouchers into one number.
        $pairs = $rows
            ->groupBy(fn ($row) => $row->voucher_number.'|'.$row->daily_transaction_id)
            ->map(fn ($group) => [
                'old' => $group->first()->voucher_number,
                'daily' => $group->first()->daily_transaction_id,
                'date' => $group->min('transaction_date'),
                'firstId' => $group->min('id'),
            ])
            ->sortBy([['date', 'asc'], ['firstId', 'asc']])
            ->values();

        $plan = [];
        foreach ($pairs as $index => $pair) {
            $plan[] = [
                'old' => $pair['old'],
                'daily' => $pair['daily'],
                'new' => $prefix.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
            ];
        }

        $label = "Scope {$prefix} {$scope['month']} unit ".($scope['unit'] ?? 'null');
        $this->line("<comment>{$label}</comment> — {$pairs->count()} vouchers:");
        $this->table(['old', 'daily_transaction_id', 'new'], $plan);

        if ($this->option('dry-run')) {
            return;
        }

        DB::transaction(function () use ($plan, $monthStart, $monthEnd, $scope) {
            foreach ($plan as $item) {
                JournalEntry::where('daily_transaction_id', $item['daily'])
                    ->where('voucher_number', $item['old'])
                    ->whereDate('transaction_date', '>=', $monthStart)
                    ->whereDate('transaction_date', '<=', $monthEnd)
                    ->when($scope['unit'] !== null,
                        fn ($query) => $query->where('business_unit_id', $scope['unit']),
                        fn ($query) => $query->whereNull('business_unit_id'))
                    ->update(['voucher_number' => $item['new']]);
            }
        });

        $this->info("{$label} repaired.");
    }
}
