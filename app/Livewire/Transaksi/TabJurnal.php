<?php

namespace App\Livewire\Transaksi;

use App\Models\JurnalUmum;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
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

    public function updating(string $name, mixed $value): void
    {
        if (in_array($name, ['unitId', 'mode', 'tanggal', 'minggu', 'bulan', 'semester', 'semesterTahun', 'tahun', 'sortField', 'sortDirection'], true)) {
            $this->resetPage();
        }
    }

    public function mount(): void
    {
        // Filter change remounts this tab via wire:key, but the page number
        // lingers in the query string (?page=3) — always start from page 1.
        $this->resetPage();
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

        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        $paginated = (clone $query)
            ->paginate(40);

        $groups = $paginated->getCollection()->groupBy('nomor_bukti');

        // We need to return an object that contains both the grouped transactions and the paginator
        return [
            'paginator' => $paginated,
            'groups' => $groups,
        ];
    }

    #[Computed]
    public function totalDebet(): float
    {
        [$start, $end] = $this->dateRange;
        $query = JurnalUmum::whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'));
        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        return (float) $query->sum('debet');
    }

    #[Computed]
    public function totalKredit(): float
    {
        [$start, $end] = $this->dateRange;
        $query = JurnalUmum::whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'));
        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        return (float) $query->sum('kredit');
    }

    #[Computed]
    public function canExportPdf(): bool
    {
        return in_array($this->mode, ['bulanan', 'semester', 'tahunan']);
    }

    /**
     * Boleh hapus: sekretaris, bendahara, direktur_bumdes, kepala_unit (own unit only)
     * Tidak boleh hapus: kepala_desa, pengawas
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

        // Fix OOM & timeout untuk data besar (ribuan baris) saat cetak PDF
        ini_set('memory_limit', '-1');
        set_time_limit(300);

        [$start, $end] = $this->dateRange;

        $sortField = in_array($this->sortField, ['tanggal', 'nomor_bukti']) ? $this->sortField : 'tanggal';
        $sortDirection = $this->sortDirection === 'desc' ? 'desc' : 'asc';

        $query = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->whereDate('tanggal', '>=', $start->format('Y-m-d'))
            ->whereDate('tanggal', '<=', $end->format('Y-m-d'))
            ->orderBy($sortField, $sortDirection)
            ->orderBy($sortField === 'tanggal' ? 'nomor_bukti' : 'tanggal', $sortDirection)
            ->orderBy('id', $sortDirection);

        if ($this->unitId) {
            $query->where('unit_wisata_id', $this->unitId);
        }

        $transactions = $query->get();
        $totalDebet = $transactions->sum('debet');
        $totalKredit = $transactions->sum('kredit');
        $unit = $this->unitId ? UnitWisata::find($this->unitId) : null;
        $periode = $this->periodeLabel;

        $pdf = Pdf::loadView('pdf.riwayat-transaksi', compact(
            'transactions',
            'periode',
            'unit',
            'totalDebet',
            'totalKredit'
        ))->setPaper('a4', 'landscape');

        $unitName = $unit ? str_replace(' ', '_', $unit->nama) : 'Semua_Unit';
        $filename = 'JurnalUmum_'.$unitName.'_'.str_replace([' ', '-', '/'], '_', $periode).'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    public function render()
    {
        return view('livewire.transaksi.tab-jurnal');
    }
}
