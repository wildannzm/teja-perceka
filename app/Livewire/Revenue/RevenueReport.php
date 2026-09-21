<?php

namespace App\Livewire\Revenue;

use App\Models\BusinessUnit;
use App\Models\DailyTransaction;
use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Pendapatan')]
class RevenueReport extends Component
{
    public $unit_id = null;

    /** 'daily' | 'weekly' | 'monthly' | 'semester' | 'yearly' */
    public string $mode = 'daily';

    /** Daily mode: specific date, format Y-m-d */
    public string $transactionDate = '';

    /** Weekly mode: Monday of the selected ISO week, format Y-m-d */
    public string $week = '';

    /** Monthly mode: format Y-m */
    public string $month = '';

    /** Semester mode */
    public string $semester = '1';

    public string $semesterYear = '';

    /** Annual mode: format Y */
    public string $year = '';

    public function mount(): void
    {
        $user = Auth::user();

        // Unit head: lock to assigned unit
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->business_unit_id;
        }

        // Default all modes to current period
        $now = Carbon::now();
        $this->transactionDate = $now->format('Y-m-d');
        $this->week = $now->startOfWeek()->format('Y-m-d');
        $this->month = $now->format('Y-m');
        $this->semesterYear = $now->format('Y');
        $this->semester = $now->month <= 6 ? '1' : '2';
        $this->year = $now->format('Y');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Check if currently selected unit is TPS (weekly input frequency).
     */
    private function isWeeklyUnit(?BusinessUnit $unit): bool
    {
        return $unit && $unit->input_frequency === 'weekly';
    }

    /**
     * Return [start, end] Carbon range based on mode and active period values.
     * Returns null for weekly TPS units under daily mode (not applicable).
     */
    private function periodRange(?BusinessUnit $unit): ?array
    {
        $isWeekly = $this->isWeeklyUnit($unit);

        switch ($this->mode) {
            case 'daily':
                // Daily mode does not apply to weekly units (TPS)
                if ($isWeekly) {
                    return null;
                }
                $date = Carbon::parse($this->transactionDate ?: Carbon::today()->format('Y-m-d'));

                return [$date->startOfDay(), $date->copy()->endOfDay()];

            case 'weekly':
                // For TPS: match exact single record with transaction_date = startOfWeek (ISO)
                // For daily units: WHERE transaction_date BETWEEN startOfWeek AND endOfWeek
                $weekStart = Carbon::parse($this->week ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd = $weekStart->copy()->endOfWeek();

                return [$weekStart, $weekEnd];

            case 'monthly':
                $date = Carbon::parse(($this->month ?: Carbon::now()->format('Y-m')).'-01');

                return [$date->startOfMonth(), $date->copy()->endOfMonth()];

            case 'semester':
                $year = (int) ($this->semesterYear ?: Carbon::now()->format('Y'));
                if ($this->semester === '1') {
                    return [
                        Carbon::create($year, 1, 1)->startOfDay(),
                        Carbon::create($year, 6, 30)->endOfDay(),
                    ];
                } else {
                    return [
                        Carbon::create($year, 7, 1)->startOfDay(),
                        Carbon::create($year, 12, 31)->endOfDay(),
                    ];
                }

            case 'yearly':
                $year = (int) ($this->year ?: Carbon::now()->format('Y'));

                return [
                    Carbon::create($year, 1, 1)->startOfDay(),
                    Carbon::create($year, 12, 31)->endOfDay(),
                ];

            default:
                return null;
        }
    }

    /**
     * Build DailyTransaction query based on mode, range, and unit.
     * TPS (weekly): query based on exact date (transaction_date = start of week).
     * Daily units: query based on date range.
     */
    private function buildDailyTransactionQuery(?BusinessUnit $unit, ?array $range)
    {
        if ($range === null) {
            // No relevant data (e.g. daily mode for weekly units)
            return DailyTransaction::query()->whereRaw('1 = 0');
        }

        [$start, $end] = $range;
        $q = DailyTransaction::query();

        // Unit filter
        if ($this->unit_id) {
            $q->where('business_unit_id', $this->unit_id);
        }

        // TPS (weekly): find exact record whose period matches current week
        // Column transaction_date = week start, end_date = week end
        if ($this->mode === 'weekly' && $this->isWeeklyUnit($unit) && $this->unit_id) {
            $q->where('transaction_date', $start->format('Y-m-d'))
                ->where('end_date', $end->format('Y-m-d'));
        } else {
            // Daily unit: WHERE transaction_date BETWEEN start AND end
            // For consolidated view, include all units including TPS by date
            $q->whereBetween('transaction_date', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        }

        return $q;
    }

    // ─── Computed ────────────────────────────────────────────────────────

    #[Computed]
    public function units(): Collection
    {
        return BusinessUnit::orderBy('name')->get();
    }

    #[Computed]
    public function selectedUnit(): ?BusinessUnit
    {
        return $this->unit_id ? BusinessUnit::find($this->unit_id) : null;
    }

    /**
     * Is daily mode active while selected unit is weekly?
     * Used to hide/disable daily mode picker in weekly views.
     */
    #[Computed]
    public function isDailyDisabled(): bool
    {
        // Daily mode is disabled/hidden when selected unit is weekly (TPS)
        return $this->isWeeklyUnit($this->selectedUnit);
    }

    #[Computed]
    public function availableWeeks(): Collection
    {
        $unit = $this->selectedUnit;
        if (! $this->isWeeklyUnit($unit)) {
            return collect();
        }

        return DailyTransaction::where('business_unit_id', $this->unit_id)
            ->select('transaction_date', 'end_date')
            ->distinct()
            ->orderBy('transaction_date', 'desc')
            ->get();
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function periodLabel(): string
    {
        $unit = $this->selectedUnit;

        switch ($this->mode) {
            case 'daily':
                $date = Carbon::parse($this->transactionDate ?: Carbon::today()->format('Y-m-d'));

                return $date->translatedFormat('d F Y');

            case 'weekly':
                $weekStart = Carbon::parse($this->week ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd = $weekStart->copy()->endOfWeek();

                return $weekStart->translatedFormat('d F Y').' – '.$weekEnd->translatedFormat('d F Y');

            case 'monthly':
                $date = Carbon::parse(($this->month ?: Carbon::now()->format('Y-m')).'-01');

                return $date->translatedFormat('F Y');

            case 'semester':
                $year = $this->semesterYear ?: Carbon::now()->format('Y');

                return 'Semester '.$this->semester.' Tahun '.$year;

            case 'yearly':
                return 'Tahun '.($this->year ?: Carbon::now()->format('Y'));

            default:
                return '-';
        }
    }

    #[Computed]
    public function reportData(): array
    {
        // Fresh lookup – do not rely on cached $this->selectedUnit
        $unit = $this->unit_id ? BusinessUnit::find($this->unit_id) : null;
        $range = $this->periodRange($unit);

        $unitName = $unit ? $unit->name : 'Semua Unit (Konsolidasi)';

        // Return empty if daily mode is selected for weekly unit (TPS)
        if ($range === null) {
            return [
                'unit' => $unitName,
                'categoryRows' => collect([]),
                'totalRevenue' => 0,
                'isEmpty' => true,
                'emptyMessage' => 'Mode Harian tidak tersedia untuk unit TPS karena data diinput per minggu. Silakan pilih mode Mingguan atau Bulanan.',
            ];
        }

        // Fetch matching DailyTransaction IDs
        $transactionIds = $this->buildDailyTransactionQuery($unit, $range)->pluck('id');

        if ($transactionIds->isEmpty()) {
            return [
                'unit' => $unitName,
                'categoryRows' => collect([]),
                'totalRevenue' => 0,
                'isEmpty' => true,
                'emptyMessage' => 'Tidak ada data pemasukan pada periode ini.',
            ];
        }

        // Aggregate details per category — single query
        $aggregatedRows = TransactionItem::whereIn('daily_transaction_id', $transactionIds)
            ->select(
                'transaction_category_id',
                DB::raw('SUM(quantity) as totalQuantity'),
                DB::raw('MAX(unit_price) as unit_price'),   // unit price is constant over the period (MAX is enough)
                DB::raw('SUM(subtotal) as totalSubtotal')
            )
            ->groupBy('transaction_category_id')
            ->with('transactionCategory.businessUnit')
            ->get();

        // Shape categoryRows into the expected format
        $categoryRows = $aggregatedRows->map(function ($row) {
            $category = $row->transactionCategory;

            return [
                'categoryId' => $category->id,
                'category' => $category->name,
                'unitName' => $category->businessUnit?->name ?? '-',
                'type' => $category->type->value,  // harga_x_qty | flat | bebas | tahunan
                'unit_price' => $category->type->value === 'harga_x_qty' ? (float) $row->unit_price : null,
                'totalQuantity' => $category->type->value === 'harga_x_qty' ? (int) $row->totalQuantity : null,
                'subtotal' => (float) $row->totalSubtotal,
            ];
        })->sortBy('category')->values();

        $totalRevenue = $categoryRows->sum('subtotal');

        return [
            'unit' => $unitName,
            'categoryRows' => $categoryRows,
            'totalRevenue' => $totalRevenue,
            'isEmpty' => $categoryRows->isEmpty(),
            'emptyMessage' => 'Tidak ada data pemasukan pada periode ini.',
        ];
    }

    public function updatedMode(): void
    {
        // When the daily mode is picked for a weekly unit, switch to weekly automatically
        $unit = $this->selectedUnit;
        if ($this->mode === 'daily' && $this->isWeeklyUnit($unit)) {
            $this->mode = 'weekly';
        }
    }

    public function updatedUnitId(): void
    {
        // When switching to TPS while still in daily mode → move to weekly automatically
        $unit = BusinessUnit::find($this->unit_id);
        if ($this->mode === 'daily' && $this->isWeeklyUnit($unit)) {
            $this->mode = 'weekly';
        }
    }

    public function render()
    {
        return view('livewire.revenue.revenue');
    }
}
