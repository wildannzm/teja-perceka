<?php

namespace App\Livewire\LaporanLabaRugi;

use App\Models\AlokasiLabaRiwayat;
use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Support\SaldoKasBumdes;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Alokasi Laba')]
class AlokasiLaba extends Component
{
    /** 'bulanan' | 'semester' | 'tahunan' */
    public string $mode = 'bulanan';

    public string $periode = '';

    public string $semester = '1';

    public string $semesterTahun = '';

    // Add Row Form
    public bool $showForm = false;

    public string $formKeterangan = '';

    public string $formKelompok = 'pengurang'; // 'pengurang' | 'ad_art'

    public string $formPersentase = '';

    public ?string $deleteKeterangan = null;

    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        // Unit heads cannot access this page via routing/middleware;
        // ensure access is denied defensively.
        if ($user->hasRole('kepala_unit')) {
            abort(403);
        }

        $now = Carbon::now();
        $this->periode = $this->mode === 'bulanan' ? $now->format('Y-m') : $now->format('Y');
        $this->semesterTahun = $now->format('Y');
        $this->semester = $now->month <= 6 ? '1' : '2';
    }

    public function updatedMode(): void
    {
        $now = Carbon::now();
        if ($this->mode === 'bulanan') {
            $this->periode = $now->format('Y-m');
        } elseif ($this->mode === 'tahunan') {
            $this->periode = $now->format('Y');
        } elseif ($this->mode === 'semester') {
            $this->semesterTahun = $now->format('Y');
            $this->semester = $now->month <= 6 ? '1' : '2';
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    private function periodeRange(): array
    {
        if ($this->mode === 'tahunan') {
            $year = (int) ($this->periode ?: Carbon::now()->format('Y'));

            return [
                Carbon::create($year, 1, 1)->startOfDay(),
                Carbon::create($year, 12, 31)->endOfDay(),
            ];
        }

        if ($this->mode === 'semester') {
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
        }

        $date = Carbon::parse($this->periode ?: Carbon::now()->format('Y-m'));

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }

    private function periodeLabel(): string
    {
        if ($this->mode === 'tahunan') {
            return 'Tahun '.($this->periode ?: Carbon::now()->format('Y'));
        }

        if ($this->mode === 'semester') {
            $year = $this->semesterTahun ?: Carbon::now()->format('Y');

            return 'Semester '.$this->semester.' Tahun '.$year;
        }

        return Carbon::parse($this->periode ?: Carbon::now()->format('Y-m'))->translatedFormat('F Y');
    }

    // ─── Calculation Data ────────────────────────────────────────────────

    private function sumAkunSaldo(string $tipe, string $arahNormal, array $range): float
    {
        [$start, $end] = $range;
        // Only calculate BUMDes transaction journals (vouchers DBM & KBM or unit_wisata_id IS NULL)
        $q = JurnalUmum::query()
            ->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->where(function ($query) {
                $query->where('nomor_bukti', 'like', 'DBM%')
                    ->orWhere('nomor_bukti', 'like', 'KBM%')
                    ->orWhereNull('unit_wisata_id');
            });

        $akunIds = KodeAkun::where('tipe', $tipe)->pluck('id');
        if ($akunIds->isEmpty()) {
            return 0;
        }

        $sums = $q->whereIn('kode_akun_id', $akunIds)
            ->selectRaw($arahNormal === 'kredit'
                ? 'SUM(kredit) - SUM(debet) as jumlah'
                : 'SUM(debet) - SUM(kredit) as jumlah')
            ->value('jumlah');

        return (float) $sums;
    }

    #[Computed]
    public function labaBersih(): float
    {
        [$start, $end] = $this->periodeRange();

        $pendapatan = $this->sumAkunSaldo('pendapatan', 'kredit', [$start, $end]) + SaldoKasBumdes::getTotalNetUnitIncome($start, $end);
        $hpp = $this->sumAkunSaldo('hpp', 'debet', [$start, $end]);
        $beban = $this->sumAkunSaldo('beban', 'debet', [$start, $end]);
        $pendapatanLain = $this->sumAkunSaldo('pendapatan_lain', 'kredit', [$start, $end]);
        $bebanLain = $this->sumAkunSaldo('beban_lain', 'debet', [$start, $end]);

        $labaKotor = $pendapatan - $hpp;

        return $labaKotor - $beban + $pendapatanLain - $bebanLain;
    }

    /**
     * Get all active allocation rows (latest per description) within period.
     * Returns raw collection with category grouping column.
     */
    #[Computed]
    public function alokasiRows(): Collection
    {
        [$start, $end] = $this->periodeRange();

        $latestRecords = AlokasiLabaRiwayat::where('berlaku_dari', '<=', $end->format('Y-m-d'))
            ->orderBy('berlaku_dari', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('keterangan');

        // Exclude rows with 0 percentage (marked as deleted)
        return $latestRecords->filter(fn ($r) => (float) $r->persentase > 0)->values();
    }

    /**
     * Deduction group rows — calculated from raw net profit.
     */
    #[Computed]
    public function pengurangRows(): Collection
    {
        $labaBersih = $this->labaBersih;

        return $this->alokasiRows
            ->where('kelompok', 'pengurang')
            ->map(function ($row) use ($labaBersih) {
                $nominal = (float) $row->persentase / 100 * $labaBersih;

                return [
                    'keterangan' => $row->keterangan,
                    'persentase' => (float) $row->persentase,
                    'nominal' => $nominal,
                ];
            })->values();
    }

    /**
     * Total amount of all deduction rows.
     */
    #[Computed]
    public function totalPengurang(): float
    {
        return $this->pengurangRows->sum('nominal');
    }

    /**
     * Net profit after subtracting all deductions.
     */
    #[Computed]
    public function labaSetelahPengurang(): float
    {
        return $this->labaBersih - $this->totalPengurang;
    }

    /**
     * AD/ART group rows — calculated from net profit after deductions.
     */
    #[Computed]
    public function adArtRows(): Collection
    {
        $base = $this->labaSetelahPengurang;

        return $this->alokasiRows
            ->where('kelompok', 'ad_art')
            ->map(function ($row) use ($base) {
                $nominal = (float) $row->persentase / 100 * $base;

                return [
                    'keterangan' => $row->keterangan,
                    'persentase' => (float) $row->persentase,
                    'nominal' => $nominal,
                ];
            })->values();
    }

    /**
     * Total percentage of all AD/ART rows.
     */
    #[Computed]
    public function totalAdArtPersen(): float
    {
        return $this->alokasiRows->where('kelompok', 'ad_art')->sum('persentase');
    }

    /**
     * Total amount of all AD/ART rows.
     */
    #[Computed]
    public function totalAdArt(): float
    {
        return $this->adArtRows->sum('nominal');
    }

    #[Computed]
    public function canEdit(): bool
    {
        // Only the director, secretary, and treasurer can edit
        return Auth::user()->hasAnyRole(['direktur_bumdes', 'sekretaris', 'bendahara']);
    }

    // ─── Actions ─────────────────────────────────────────────────────────

    public function simpanBaris(): void
    {
        if (! $this->canEdit) {
            abort(403);
        }

        $this->validate([
            'formKeterangan' => 'required|string|max:100',
            'formKelompok' => 'required|in:pengurang,ad_art',
            'formPersentase' => 'required|numeric|min:0.01|max:100',
        ]);

        [$start, $end] = $this->periodeRange();

        AlokasiLabaRiwayat::create([
            'keterangan' => $this->formKeterangan,
            'persentase' => (float) $this->formPersentase,
            'kelompok' => $this->formKelompok,
            'berlaku_dari' => $start->format('Y-m-d'),
            'unit_wisata_id' => null, // Global level
        ]);

        $this->reset(['formKeterangan', 'formKelompok', 'formPersentase', 'showForm']);
        $this->formKelompok = 'pengurang'; // Reset to default
        \Flux::toast(variant: 'success', text: 'Baris alokasi berhasil ditambahkan.');
    }

    public function confirmDelete(string $keterangan): void
    {
        $this->deleteKeterangan = $keterangan;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->canEdit || ! $this->deleteKeterangan) {
            abort(403);
        }

        [$start, $end] = $this->periodeRange();

        // Set percentage = 0 to mark as deleted (immutable audit history)
        AlokasiLabaRiwayat::create([
            'keterangan' => $this->deleteKeterangan,
            'persentase' => 0,
            'kelompok' => 'pengurang', // Category is irrelevant on deletion
            'berlaku_dari' => $start->format('Y-m-d'),
            'unit_wisata_id' => null,
        ]);

        \Flux::toast(variant: 'success', text: 'Baris alokasi berhasil dihapus untuk periode ini.');

        $this->showDeleteModal = false;
        $this->deleteKeterangan = null;
    }

    // ─── Export PDF ──────────────────────────────────────────────────────

    public function exportPdf()
    {
        $namaEntitas = 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodeRange();
        $tanggalCetak = strtoupper($end->translatedFormat('d F Y'));
        $tanggalTtd = $end->translatedFormat('F Y');
        $periodeLabel = $this->periodeLabel();

        $penandatangan = Auth::user()->name;
        $jabatan = match (true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            default => '',
        };

        $labaBersih = $this->labaBersih;
        $pengurangRows = $this->pengurangRows;
        $totalPengurang = $this->totalPengurang;
        $labaSetelahPengurang = $this->labaSetelahPengurang;
        $adArtRows = $this->adArtRows;
        $totalAdArtPersen = $this->totalAdArtPersen;
        $totalAdArt = $this->totalAdArt;

        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $pdf = Pdf::loadView('pdf.alokasi-laba', compact(
            'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'periodeLabel', 'penandatangan', 'jabatan',
            'labaBersih', 'pengurangRows', 'totalPengurang', 'labaSetelahPengurang',
            'adArtRows', 'totalAdArtPersen', 'totalAdArt'
        ))->setPaper('a4', 'portrait');

        $filename = 'AlokasiLaba_BUMDes_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.laporan-laba-rugi.alokasi-laba');
    }
}
