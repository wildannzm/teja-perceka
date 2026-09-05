<?php

namespace App\Livewire\LaporanLabaRugi;

use App\Models\AlokasiLabaRiwayat;
use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
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
    public $unit_id = null;

    /** 'bulanan' | 'semester' | 'tahunan' */
    public string $mode = 'bulanan';

    public string $periode = '';

    public string $semester = '1';

    public string $semesterTahun = '';

    // Form Tambah Baris
    public bool $showForm = false;

    public string $formKeterangan = '';

    public string $formKelompok = 'pengurang'; // 'pengurang' | 'ad_art'

    public string $formPersentase = '';

    public ?string $deleteKeterangan = null;

    public bool $showDeleteModal = false;

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit tidak bisa akses halaman ini via routing/middleware,
        // tapi kita pastikan aman.
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

    // ─── Data Perhitungan ────────────────────────────────────────────────

    #[Computed]
    public function units(): \Illuminate\Database\Eloquent\Collection
    {
        return UnitWisata::orderBy('nama')->get();
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return $this->unit_id ? UnitWisata::find($this->unit_id) : null;
    }

    private function sumAkunSaldo(string $tipe, string $arahNormal, array $range): float
    {
        [$start, $end] = $range;
        $q = JurnalUmum::query()->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        if ($this->unit_id) {
            $q->where('unit_wisata_id', $this->unit_id);
        }

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
        $range = $this->periodeRange();

        $pendapatan = $this->sumAkunSaldo('pendapatan', 'kredit', $range);
        $hpp = $this->sumAkunSaldo('hpp', 'debet', $range);
        $beban = $this->sumAkunSaldo('beban', 'debet', $range);
        $pendapatanLain = $this->sumAkunSaldo('pendapatan_lain', 'kredit', $range);
        $bebanLain = $this->sumAkunSaldo('beban_lain', 'debet', $range);

        $labaKotor = $pendapatan - $hpp;

        return $labaKotor - $beban + $pendapatanLain - $bebanLain;
    }

    /**
     * Ambil semua baris alokasi aktif (terbaru per keterangan) pada periode.
     * Mengembalikan collection mentah dengan kolom kelompok.
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

        // Hapus yang persentasenya 0 (artinya dihapus)
        return $latestRecords->filter(fn ($r) => (float) $r->persentase > 0)->values();
    }

    /**
     * Baris kelompok "Pengurang" — dihitung dari Laba Bersih asli.
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
     * Total nominal dari semua baris Pengurang.
     */
    #[Computed]
    public function totalPengurang(): float
    {
        return $this->pengurangRows->sum('nominal');
    }

    /**
     * Laba Bersih setelah dikurangi semua baris Pengurang.
     */
    #[Computed]
    public function labaSetelahPengurang(): float
    {
        return $this->labaBersih - $this->totalPengurang;
    }

    /**
     * Baris kelompok "AD/ART" — dihitung dari Laba Bersih setelah Pengurang.
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
     * Total persentase semua baris AD/ART.
     */
    #[Computed]
    public function totalAdArtPersen(): float
    {
        return $this->alokasiRows->where('kelompok', 'ad_art')->sum('persentase');
    }

    /**
     * Total nominal semua baris AD/ART.
     */
    #[Computed]
    public function totalAdArt(): float
    {
        return $this->adArtRows->sum('nominal');
    }

    #[Computed]
    public function canEdit(): bool
    {
        // Hanya Direktur, Sekretaris, Bendahara yang bisa edit
        return Auth::user()->hasAnyRole(['direktur_bumdes', 'sekretaris', 'bendahara']);
    }

    // ─── Aksi ────────────────────────────────────────────────────────────

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
        $this->formKelompok = 'pengurang'; // reset ke default
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

        // Set persentase = 0 untuk menandakan dihapus (immutable history)
        AlokasiLabaRiwayat::create([
            'keterangan' => $this->deleteKeterangan,
            'persentase' => 0,
            'kelompok' => 'pengurang', // kelompok tidak relevan saat hapus
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
        $unit = $this->selectedUnit;
        $namaEntitas = $unit ? 'WISATA '.strtoupper($unit->nama) : 'BUMDESA TEJA PERCEKA';

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

        $unitSlug = $unit ? str_replace(' ', '_', $unit->nama) : 'Konsolidasi';
        $filename = 'AlokasiLaba_'.$unitSlug.'_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.laporan-laba-rugi.alokasi-laba');
    }
}
