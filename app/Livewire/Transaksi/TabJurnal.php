<?php

namespace App\Livewire\Transaksi;

use App\Models\JurnalUmum;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use App\Support\SaldoKasBumdes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Reactive;
use Livewire\Component;
use Livewire\Features\SupportPagination\WithoutUrlPagination;
use Livewire\WithPagination;

class TabJurnal extends Component
{
    use WithoutUrlPagination, WithPagination;

    #[Reactive]
    public $unitId = null;

    #[Reactive]
    public string $mode = 'harian';

    #[Reactive]
    public string $tanggal = '';

    #[Reactive]
    public string $minggu = '';

    #[Reactive]
    public string $bulan = '';

    #[Reactive]
    public string $semester = '1';

    #[Reactive]
    public string $semesterTahun = '';

    #[Reactive]
    public string $tahun = '';

    #[Reactive]
    public string $sortField = 'tanggal';

    #[Reactive]
    public string $sortDirection = 'asc';

    /** Journal view: 'summary' (aggregated per description) or 'detailed' (per voucher). Set from the parent filter. */
    #[Reactive]
    public string $viewMode = 'summary';

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['unitId', 'mode', 'tanggal', 'minggu', 'bulan', 'semester', 'semesterTahun', 'tahun', 'sortField', 'sortDirection', 'viewMode'], true)) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        // Filter change remounts this tab via wire:key, but the page number
        // lingers in the query string (?page=3) — always start from page 1.
        $this->resetPage();
    }

    /**
     * Check whether current view is within BUMDes scope.
     */
    public function isBumdesScope(): bool
    {
        return ! Auth::user()?->hasRole('kepala_unit') && ($this->unitId === 'bumdes' || empty($this->unitId));
    }

    /**
     * Apply query scope based on user role and selected unit:
     * - kepala_unit: restricted to their assigned unit
     * - non-kepala-unit:
     *   - 'bumdes' or default: BUMDes expenses (KBM) and direct BUMDes journals (DBM)
     *   - 'semua': all business unit journals (where unit_wisata_id is not null)
     *   - numeric ID: specific business unit journal
     */
    private function applyRoleScope($query)
    {
        if (Auth::user()?->hasRole('kepala_unit')) {
            if ($this->unitId && is_numeric($this->unitId)) {
                $query->where('unit_wisata_id', (int) $this->unitId);
            }

            return $query;
        }

        if ($this->unitId === 'semua') {
            $query->whereNotNull('unit_wisata_id');
        } elseif ($this->unitId && is_numeric($this->unitId)) {
            $query->where('unit_wisata_id', (int) $this->unitId);
        } else {
            // 'bumdes' or default: BUMDes expenses and direct BUMDes journals
            $query->where(function ($q) {
                $q->where('nomor_bukti', 'like', 'KBM%')
                    ->orWhere(function ($sub) {
                        $sub->where('nomor_bukti', 'like', 'DBM%')
                            ->whereNull('transaksi_harian_id')
                            ->whereNull('unit_wisata_id');
                    });
            });
        }

        return $query;
    }

    // ── Delete state ──────────────────────────────────────────────────────────
    public ?int $deleteId = null;

    public bool $showDeleteModal = false;

    // ── Edit state ────────────────────────────────────────────────────────────
    public ?int $editJurnalId = null;

    public string $editVoucherNumber = '';

    public bool $showEditModal = false;

    /** Editable header fields */
    public string $editTanggal = '';

    public string $editKeterangan = '';

    /**
     * Editable rows per jurnal entry within the nomor_bukti group.
     * Each row: ['id' => int, 'kode_akun_id' => int, 'kode_akun_label' => string, 'debet' => string, 'kredit' => string]
     *
     * @var array<int, array{id: int, kode_akun_id: int, kode_akun_label: string, debet: string, kredit: string}>
     */
    public array $editRows = [];

    #[Computed]
    public function dateRange(): array
    {
        switch ($this->mode) {
            case 'harian':
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));

                return [$date->copy()->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                $start = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));

                return [$start->copy()->startOfWeek(), $start->copy()->endOfWeek()];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')).'-01');

                return [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()];

            case 'semester':
                $year = (int) ($this->semesterTahun ?: Carbon::now()->format('Y'));
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

            case 'tahunan':
                $year = (int) ($this->tahun ?: Carbon::now()->format('Y'));

                return [Carbon::create($year, 1, 1)->startOfDay(), Carbon::create($year, 12, 31)->endOfDay()];
        }

        $today = Carbon::today();

        return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
    }

    #[Computed]
    public function periodeLabel(): string
    {
        [$start, $end] = $this->dateRange;

        return match ($this->mode) {
            'harian' => $start->translatedFormat('d F Y'),
            'mingguan' => $start->month === $end->month
                ? $start->format('d').' - '.$end->translatedFormat('d F Y')
                : $start->translatedFormat('d M').' - '.$end->translatedFormat('d M Y'),
            'bulanan' => $start->translatedFormat('F Y'),
            'semester' => 'Semester '.$this->semester.' Tahun '.($this->semesterTahun ?: Carbon::now()->format('Y')),
            'tahunan' => $start->format('Y'),
            default => '-',
        };
    }

    /**
     * Voucher group key: number + month + unit. Voucher numbers reset every
     * month per unit, so the number alone is not unique across months
     * (groups would merge in semester/yearly filters).
     */
    private function voucherGroupKey($jurnal): string
    {
        return $jurnal->nomor_bukti.'|'.Carbon::parse($jurnal->tanggal)->format('Y-m').'|'.($jurnal->unit_wisata_id ?? 'null');
    }

    #[Computed]
    public function transactions()
    {
        [$start, $end] = $this->dateRange;

        $sortField = in_array($this->sortField, ['tanggal', 'nomor_bukti']) ? $this->sortField : 'tanggal';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'))
            ->orderBy($sortField, $sortDirection)
            ->orderBy($sortField === 'tanggal' ? 'nomor_bukti' : 'tanggal', $sortDirection)
            ->orderBy('id', $sortDirection);

        $this->applyRoleScope($query);

        if ($this->isBumdesScope()) {
            $dbTransactions = $query->get();
            $allTransactions = SaldoKasBumdes::attachToTransactions(
                $dbTransactions,
                $start,
                $end,
                $this->mode,
                $sortField,
                $sortDirection
            );

            $page = LengthAwarePaginator::resolveCurrentPage();
            $perPage = 40;
            $items = $allTransactions->slice(($page - 1) * $perPage, $perPage)->values();
            $paginated = new LengthAwarePaginator(
                $items,
                $allTransactions->count(),
                $perPage,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );

            $groups = $items->groupBy(fn ($jurnal) => $this->voucherGroupKey($jurnal));

            return [
                'paginator' => $paginated,
                'groups' => $groups,
            ];
        }

        $paginated = (clone $query)
            ->paginate(40);

        $groups = $paginated->getCollection()->groupBy(fn ($jurnal) => $this->voucherGroupKey($jurnal));

        // We need to return an object that contains both the grouped transactions and the paginator
        return [
            'paginator' => $paginated,
            'groups' => $groups,
        ];
    }

    /**
     * Excel-style summary view: DB aggregation per (description + account + unit)
     * within the period. Stored rows stay detailed, saved numbers untouched.
     * Display date = period end date for every row.
     * Rows sharing (description + first voucher + unit) merge into one group
     * (rowspan like the detailed view), groups ordered by voucher number.
     *
     * @return array{paginator: LengthAwarePaginator, groups: Collection, displayDate: string}
     */
    #[Computed]
    public function summaryRows(): array
    {
        ['groups' => $groups, 'displayDate' => $displayDate] = $this->buildSummaryGroups();

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 20;
        $items = $groups->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator(
            $items,
            $groups->count(),
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return [
            'paginator' => $paginated,
            'groups' => $items,
            'displayDate' => $displayDate,
        ];
    }

    /**
     * Full summary groups without pagination (one screen page + PDF).
     *
     * @return array{groups: Collection, displayDate: string}
     */
    private function buildSummaryGroups(): array
    {
        [$start, $end] = $this->dateRange;

        $key = DB::raw('TRIM(jurnal_umum.keterangan)');

        $query = JurnalUmum::query()
            ->leftJoin('kode_akun', 'kode_akun.id', '=', 'jurnal_umum.kode_akun_id')
            ->leftJoin('unit_wisata', 'unit_wisata.id', '=', 'jurnal_umum.unit_wisata_id')
            ->selectRaw('MIN(jurnal_umum.id) as id, TRIM(jurnal_umum.keterangan) as keterangan, jurnal_umum.kode_akun_id, jurnal_umum.unit_wisata_id, kode_akun.kode as kode, kode_akun.nama as accountName, unit_wisata.nama as unitName, SUM(jurnal_umum.debet) as totalDebit, SUM(jurnal_umum.kredit) as totalCredit, COUNT(*) as rowCount, COUNT(DISTINCT jurnal_umum.nomor_bukti) as voucherCount, MIN(jurnal_umum.nomor_bukti) as firstVoucher, MAX(jurnal_umum.nomor_bukti) as lastVoucher')
            ->whereBetween('jurnal_umum.tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->groupBy($key, 'jurnal_umum.kode_akun_id', 'jurnal_umum.unit_wisata_id', 'kode_akun.kode', 'kode_akun.nama', 'unit_wisata.nama', DB::raw('SUBSTR(jurnal_umum.tanggal, 1, 7)'));

        $this->applyRoleScope($query);

        $rows = $query->get();

        // Voucher numbers reset every month: collapse monthly rows so the
        // voucher count spans months (per-month DISTINCT counts summed).
        $rows = $rows
            ->groupBy(fn ($row) => mb_strtolower(trim((string) $row->keterangan)).'|'.$row->kode_akun_id.'|'.($row->unit_wisata_id ?? 'null'))
            ->map(function ($monthRows) {
                $first = $monthRows->first();
                $first->totalDebit = (float) $monthRows->sum('totalDebit');
                $first->totalCredit = (float) $monthRows->sum('totalCredit');
                $first->rowCount = (int) $monthRows->sum('rowCount');
                $first->voucherCount = (int) $monthRows->sum('voucherCount');
                $first->firstVoucher = $monthRows->min('firstVoucher');
                $first->lastVoucher = $monthRows->max('lastVoucher');

                return $first;
            })
            ->values();

        if ($this->isBumdesScope()) {
            $rows = $this->mergeVirtualSummary($rows, $start, $end);
        }

        $desc = $this->sortDirection === 'desc';

        $groups = $rows
            ->groupBy(fn ($row) => mb_strtolower(trim((string) $row->keterangan)).'|'.($row->firstVoucher ?? '').'|'.($row->unit_wisata_id ?? 'null'))
            ->map(fn ($group) => $group->sortByDesc(fn ($row) => (float) $row->totalDebit > 0)->values())
            ->sortBy(fn ($group) => ($group->first()->firstVoucher ?? '').'|'.mb_strtolower(trim((string) $group->first()->keterangan)), SORT_STRING, $desc)
            ->values();

        return [
            'groups' => $groups,
            'displayDate' => $end->format('Y-m-d'),
        ];
    }

    /**
     * Merge BUMDes virtual entries (opening balance + unit revenue) into summary rows.
     *
     * @param  Collection<int, object>  $rows
     * @return Collection<int, object>
     */
    private function mergeVirtualSummary($rows, Carbon $start, Carbon $end)
    {
        $virtuals = SaldoKasBumdes::getBumdesVirtualEntries($start, $end, $this->mode);
        if ($virtuals->isEmpty()) {
            return $rows->values();
        }

        $grouped = [];
        foreach ($virtuals as $entry) {
            $keterangan = trim((string) $entry->keterangan);
            $mapKey = mb_strtolower($keterangan).'|'.$entry->kode_akun_id.'|'.($entry->unit_wisata_id ?? 'null');
            if (! isset($grouped[$mapKey])) {
                $grouped[$mapKey] = (object) [
                    'id' => 0,
                    'keterangan' => $keterangan,
                    'kode_akun_id' => $entry->kode_akun_id,
                    'unit_wisata_id' => $entry->unit_wisata_id,
                    'kode' => $entry->kodeAkun?->kode,
                    'accountName' => $entry->kodeAkun?->nama,
                    'unitName' => $entry->unitWisata?->nama,
                    'totalDebit' => 0.0,
                    'totalCredit' => 0.0,
                    'rowCount' => 0,
                    'voucherCount' => 0,
                    'firstVoucher' => $entry->nomor_bukti,
                    'lastVoucher' => $entry->nomor_bukti,
                ];
            }
            $row = $grouped[$mapKey];
            $row->totalDebit += (float) $entry->debet;
            $row->totalCredit += (float) $entry->kredit;
            $row->rowCount++;
            $row->firstVoucher = min($row->firstVoucher, $entry->nomor_bukti);
            $row->lastVoucher = max($row->lastVoucher, $entry->nomor_bukti);
        }

        $merged = $rows->all();
        foreach ($grouped as $mapKey => $virtual) {
            $found = false;
            foreach ($merged as $row) {
                if (mb_strtolower(trim((string) $row->keterangan)).'|'.$row->kode_akun_id.'|'.($row->unit_wisata_id ?? 'null') === $mapKey) {
                    $row->totalDebit += $virtual->totalDebit;
                    $row->totalCredit += $virtual->totalCredit;
                    $row->rowCount += $virtual->rowCount;
                    $row->voucherCount += 1;
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                $virtual->voucherCount = 1;
                $merged[] = $virtual;
            }
        }

        return collect($merged)->sort(function ($a, $b) {
            $cmp = strcmp((string) ($a->firstVoucher ?? ''), (string) ($b->firstVoucher ?? ''));
            if ($cmp !== 0) {
                return $this->sortDirection === 'desc' ? -$cmp : $cmp;
            }
            $cmp = strcmp(mb_strtolower((string) $a->keterangan), mb_strtolower((string) $b->keterangan));

            return $this->sortDirection === 'desc' ? -$cmp : $cmp;
        })->values();
    }

    #[Computed]
    public function totalDebet(): float
    {
        [$start, $end] = $this->dateRange;
        $query = JurnalUmum::whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'));
        $this->applyRoleScope($query);

        $total = (float) $query->sum('debet');
        if ($this->isBumdesScope()) {
            if (in_array($this->mode, ['bulanan', 'semester', 'tahunan'], true)) {
                $total += SaldoKasBumdes::getOpeningBalance($start);
            }
            $total += SaldoKasBumdes::getTotalNetUnitIncome($start, $end);
        }

        return $total;
    }

    #[Computed]
    public function totalKredit(): float
    {
        [$start, $end] = $this->dateRange;
        $query = JurnalUmum::whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'));
        $this->applyRoleScope($query);

        $total = (float) $query->sum('kredit');
        if ($this->isBumdesScope()) {
            if (in_array($this->mode, ['bulanan', 'semester', 'tahunan'], true)) {
                $total += SaldoKasBumdes::getOpeningBalance($start);
            }
            $total += SaldoKasBumdes::getTotalNetUnitIncome($start, $end);
        }

        return $total;
    }

    #[Computed]
    public function canExportPdf(): bool
    {
        return in_array($this->mode, ['bulanan', 'semester', 'tahunan']);
    }

    /**
     * Authorized to delete: sekretaris, bendahara, direktur_bumdes, kepala_unit (own unit only).
     * Disallowed from deleting: kepala_desa, pengawas.
     */
    #[Computed]
    public function canDelete(): bool
    {
        return Auth::user()->hasAnyRole(['sekretaris', 'bendahara', 'direktur_bumdes', 'kepala_unit']);
    }

    /**
     * Same authorization as canDelete.
     */
    #[Computed]
    public function canEdit(): bool
    {
        return Auth::user()->hasAnyRole(['sekretaris', 'bendahara', 'direktur_bumdes', 'kepala_unit']);
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->canDelete || ! $this->deleteId) {
            abort(403);
        }

        $jurnal = JurnalUmum::find($this->deleteId);
        if (! $jurnal) {
            return;
        }

        // Unit heads: only delete own unit's journals
        if (Auth::user()->hasRole('kepala_unit')) {
            if ($jurnal->unit_wisata_id !== Auth::user()->unit_wisata_id) {
                abort(403);
            }
        }

        DB::transaction(function () use ($jurnal) {
            if ($jurnal->transaksi_harian_id) {
                TransaksiHarian::where('id', $jurnal->transaksi_harian_id)->delete();
            }
            // Same voucher scope as openEdit: number + month + unit.
            $monthStart = $jurnal->tanggal->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $jurnal->tanggal->copy()->endOfMonth()->format('Y-m-d');
            JurnalUmum::where('nomor_bukti', $jurnal->nomor_bukti)
                ->whereBetween('tanggal', [$monthStart, $monthEnd])
                ->when($jurnal->unit_wisata_id !== null,
                    fn ($query) => $query->where('unit_wisata_id', $jurnal->unit_wisata_id),
                    fn ($query) => $query->whereNull('unit_wisata_id'))
                ->delete();
        });

        $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Satu set jurnal (debet & kredit) berhasil dihapus.');

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    // ── Edit ──────────────────────────────────────────────────────────────────

    public function openEdit(int $jurnalId): void
    {
        if (! $this->canEdit) {
            abort(403);
        }

        $jurnal = JurnalUmum::findOrFail($jurnalId);

        // Unit heads: only edit own unit's journals
        if (Auth::user()->hasRole('kepala_unit')) {
            if ($jurnal->unit_wisata_id !== Auth::user()->unit_wisata_id) {
                abort(403);
            }
        }

        // Load the voucher group: same number + same month + same unit
        // (numbers reset monthly, so the number alone is not unique).
        $monthStart = $jurnal->tanggal->copy()->startOfMonth()->format('Y-m-d');
        $monthEnd = $jurnal->tanggal->copy()->endOfMonth()->format('Y-m-d');
        $group = JurnalUmum::with('kodeAkun')
            ->where('nomor_bukti', $jurnal->nomor_bukti)
            ->whereBetween('tanggal', [$monthStart, $monthEnd])
            ->when($jurnal->unit_wisata_id !== null,
                fn ($query) => $query->where('unit_wisata_id', $jurnal->unit_wisata_id),
                fn ($query) => $query->whereNull('unit_wisata_id'))
            ->orderBy('id', 'asc')
            ->get();

        $this->editJurnalId = $jurnalId;
        $this->editVoucherNumber = $jurnal->nomor_bukti;
        $this->editTanggal = $jurnal->tanggal->format('Y-m-d');
        $this->editKeterangan = $jurnal->keterangan;

        $this->editRows = $group->map(fn ($row) => [
            'id' => $row->id,
            'kode_akun_id' => $row->kode_akun_id,
            'kode_akun_label' => ($row->kodeAkun?->kode ?? '-').' - '.($row->kodeAkun?->nama ?? '?'),
            'debet' => $row->debet > 0 ? (string) (int) $row->debet : '',
            'kredit' => $row->kredit > 0 ? (string) (int) $row->kredit : '',
        ])->toArray();

        $this->showEditModal = true;
    }

    public function executeEdit(): void
    {
        if (! $this->canEdit || ! $this->editVoucherNumber) {
            abort(403);
        }

        $this->validate([
            'editTanggal' => 'required|date',
            'editKeterangan' => 'required|string|max:500',
            'editRows' => 'required|array|min:1',
            'editRows.*.debet' => 'nullable|numeric|min:0',
            'editRows.*.kredit' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () {
                foreach ($this->editRows as $row) {
                    $jurnal = JurnalUmum::findOrFail($row['id']);

                    // Authorization check per-row
                    if (Auth::user()->hasRole('kepala_unit')) {
                        if ($jurnal->unit_wisata_id !== Auth::user()->unit_wisata_id) {
                            abort(403);
                        }
                    }

                    $debet = (float) ($row['debet'] ?: 0);
                    $kredit = (float) ($row['kredit'] ?: 0);

                    $jurnal->update([
                        'tanggal' => $this->editTanggal,
                        'keterangan' => $this->editKeterangan,
                        'debet' => $debet,
                        'kredit' => $kredit,
                    ]);
                }

                // Sync the linked TransaksiHarian from the edited rows only
                // (the voucher number alone is not unique across months).
                $editedIds = collect($this->editRows)->pluck('id')->all();
                $firstJurnal = JurnalUmum::whereIn('id', $editedIds)->first();
                if ($firstJurnal && $firstJurnal->transaksi_harian_id) {
                    $totalDebet = JurnalUmum::whereIn('id', $editedIds)->sum('debet');
                    TransaksiHarian::where('id', $firstJurnal->transaksi_harian_id)->update([
                        'tanggal' => $this->editTanggal,
                        'total_pemasukan' => $totalDebet,
                    ]);
                }
            });

            $this->dispatch('swal-alert', icon: 'success', title: 'Berhasil', text: 'Jurnal berhasil diperbarui.');

            $this->showEditModal = false;
            $this->resetEditState();
            unset($this->transactions);
        } catch (\Exception $e) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan: '.$e->getMessage());
        }
    }

    public function cancelEdit(): void
    {
        $this->showEditModal = false;
        $this->resetEditState();
    }

    private function resetEditState(): void
    {
        $this->editJurnalId = null;
        $this->editVoucherNumber = '';
        $this->editTanggal = '';
        $this->editKeterangan = '';
        $this->editRows = [];
    }

    public function exportPdf()
    {
        if (! $this->canExportPdf) {
            $this->dispatch('swal-alert', icon: 'warning', title: 'Perhatian', text: 'Cetak PDF hanya tersedia untuk mode Bulanan, Semester, dan Tahunan.');

            return;
        }

        if (! class_exists(Pdf::class)) {
            $this->dispatch('swal-alert', icon: 'error', title: 'Error', text: 'Package PDF belum terinstall.');

            return;
        }

        // Prevent OOM & timeout for large datasets (thousands of rows) during PDF export
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $unit = ($this->unitId && is_numeric($this->unitId)) ? UnitWisata::find($this->unitId) : null;
        $periode = $this->periodeLabel;
        $unitLabel = $unit ? str_replace(' ', '_', $unit->nama) : ($this->isBumdesScope() ? 'BUMDes' : 'Semua_Unit');

        if ($this->viewMode === 'summary') {
            ['groups' => $groups, 'displayDate' => $displayDate] = $this->buildSummaryGroups();
            $totalDebit = (float) $groups->flatten()->sum('totalDebit');
            $totalCredit = (float) $groups->flatten()->sum('totalCredit');

            $pdf = Pdf::loadView('pdf.riwayat-transaksi-ringkas', compact(
                'groups',
                'displayDate',
                'periode',
                'unit',
                'totalDebit',
                'totalCredit'
            ))->setPaper('a4', 'landscape');

            $filename = 'JurnalUmum_Summary_'.$unitLabel.'_'.str_replace([' ', '-', '/'], '_', $periode).'.pdf';

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, $filename);
        }

        [$start, $end] = $this->dateRange;

        $sortField = in_array($this->sortField, ['tanggal', 'nomor_bukti']) ? $this->sortField : 'tanggal';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'))
            ->orderBy($sortField, $sortDirection)
            ->orderBy($sortField === 'tanggal' ? 'nomor_bukti' : 'tanggal', $sortDirection)
            ->orderBy('id', $sortDirection);

        $this->applyRoleScope($query);

        $transactions = $query->get();
        if ($this->isBumdesScope()) {
            $transactions = SaldoKasBumdes::attachToTransactions(
                $transactions,
                $start,
                $end,
                $this->mode,
                $sortField,
                $sortDirection
            );
        }
        $totalDebet = $transactions->sum('debet');
        $totalKredit = $transactions->sum('kredit');

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'totalDebet',
            'totalKredit'
        ))->setPaper('a4', 'landscape');

        $filename = 'JurnalUmum_Detailed_'.$unitLabel.'_'.str_replace([' ', '-', '/'], '_', $periode).'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.transaksi.tab-jurnal');
    }
}
