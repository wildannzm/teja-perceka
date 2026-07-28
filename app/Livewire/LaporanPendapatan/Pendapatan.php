<?php

namespace App\Livewire\LaporanPendapatan;

use App\Models\KategoriTransaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
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
class Pendapatan extends Component
{
    /** null = konsolidasi semua unit */
    public ?int $unit_id = null;

    /** harian|mingguan|bulanan|tahunan */
    public string $mode = 'harian';

    /** Mode harian: tanggal spesifik, format Y-m-d */
    public string $tanggal = '';

    /** Mode mingguan: tanggal Senin dari minggu yang dipilih, format Y-m-d (ISO week start) */
    public string $minggu = '';

    /** Mode bulanan: format Y-m */
    public string $bulan = '';

    /** Mode tahunan: format Y */
    public string $tahun = '';

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        // Default semua mode ke periode berjalan
        $now = Carbon::now();
        $this->tanggal = $now->format('Y-m-d');
        $this->minggu  = $now->startOfWeek()->format('Y-m-d');
        $this->bulan   = Carbon::now()->format('Y-m');
        $this->tahun   = Carbon::now()->format('Y');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────

    /**
     * Apakah unit yang sedang dipilih adalah TPS (frekuensi mingguan)?
     */
    private function isUnitMingguan(?UnitWisata $unit): bool
    {
        return $unit && $unit->frekuensi_input === 'mingguan';
    }

    /**
     * Kembalikan range [start, end] Carbon berdasarkan mode dan nilai periode aktif.
     * Untuk TPS dengan mode harian → return null (tidak relevan).
     */
    private function periodeRange(?UnitWisata $unit): ?array
    {
        $isTps = $this->isUnitMingguan($unit);

        switch ($this->mode) {
            case 'harian':
                // Mode harian tidak berlaku untuk unit mingguan (TPS)
                if ($isTps) {
                    return null;
                }
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));
                return [$date->startOfDay(), $date->copy()->endOfDay()];

            case 'mingguan':
                // Untuk TPS: ambil tepat 1 record dengan tanggal = startOfWeek (ISO)
                // Untuk unit harian: WHERE tanggal BETWEEN startOfWeek AND endOfWeek
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd   = $weekStart->copy()->endOfWeek();
                return [$weekStart, $weekEnd];

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')) . '-01');
                return [$date->startOfMonth(), $date->copy()->endOfMonth()];

            case 'tahunan':
                $year = (int)($this->tahun ?: Carbon::now()->format('Y'));
                return [
                    Carbon::create($year, 1, 1)->startOfDay(),
                    Carbon::create($year, 12, 31)->endOfDay(),
                ];

            default:
                return null;
        }
    }

    /**
     * Bangun query transaksi_harian berdasarkan mode, range, dan unit.
     * TPS mingguan: query berdasarkan tanggal exact (tanggal = start of week).
     * Unit harian: query berdasarkan range tanggal.
     */
    private function buildTransaksiHarianQuery(?UnitWisata $unit, ?array $range)
    {
        if ($range === null) {
            // Tidak ada data yang relevan (mis. mode harian untuk TPS)
            return TransaksiHarian::query()->whereRaw('1 = 0');
        }

        [$start, $end] = $range;
        $q = TransaksiHarian::query();

        // Filter unit
        if ($this->unit_id) {
            $q->where('unit_wisata_id', $this->unit_id);
        }

        // TPS (mingguan): cari tepat record yang periodenya = minggu ini
        // Kolom tanggal = awal minggu, tanggal_akhir = akhir minggu
        if ($this->mode === 'mingguan' && $this->isUnitMingguan($unit) && $this->unit_id) {
            $q->where('tanggal', $start->format('Y-m-d'))
              ->where('tanggal_akhir', $end->format('Y-m-d'));
        } else {
            // Unit harian: WHERE tanggal BETWEEN start AND end
            // Untuk konsolidasi, ikutkan semua unit termasuk TPS berdasar tanggal
            $q->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        }

        return $q;
    }

    // ─── Computed ────────────────────────────────────────────────────────

    #[Computed]
    public function units(): Collection
    {
        return UnitWisata::orderBy('nama')->get();
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return $this->unit_id ? UnitWisata::find($this->unit_id) : null;
    }

    /**
     * Apakah mode harian sedang aktif untuk unit yang dipilih dan unit tersebut mingguan?
     * Digunakan untuk menyembunyikan input mode harian di view TPS.
     */
    #[Computed]
    public function isHarianDisabled(): bool
    {
        // Mode harian disembunyikan/dinonaktifkan jika unit yang dipilih adalah mingguan (TPS)
        return $this->isUnitMingguan($this->selectedUnit);
    }

    #[Computed]
    public function availableTpsWeeks(): Collection
    {
        $unit = $this->selectedUnit;
        if (!$this->isUnitMingguan($unit)) {
            return collect();
        }

        return TransaksiHarian::where('unit_wisata_id', $this->unit_id)
            ->select('tanggal', 'tanggal_akhir')
            ->distinct()
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function periodeLabel(): string
    {
        $unit = $this->selectedUnit;

        switch ($this->mode) {
            case 'harian':
                $date = Carbon::parse($this->tanggal ?: Carbon::today()->format('Y-m-d'));
                return $date->translatedFormat('d F Y');

            case 'mingguan':
                $weekStart = Carbon::parse($this->minggu ?: Carbon::now()->startOfWeek()->format('Y-m-d'));
                $weekEnd   = $weekStart->copy()->endOfWeek();
                return $weekStart->translatedFormat('d F Y') . ' – ' . $weekEnd->translatedFormat('d F Y');

            case 'bulanan':
                $date = Carbon::parse(($this->bulan ?: Carbon::now()->format('Y-m')) . '-01');
                return $date->translatedFormat('F Y');

            case 'tahunan':
                return 'Tahun ' . ($this->tahun ?: Carbon::now()->format('Y'));

            default:
                return '-';
        }
    }

    #[Computed]
    public function reportData(): array
    {
        // Fresh lookup – jangan andalkan $this->selectedUnit yang sudah di-cache
        $unit  = $this->unit_id ? UnitWisata::find($this->unit_id) : null;
        $range = $this->periodeRange($unit);

        $namaUnit = $unit ? $unit->nama : 'Semua Unit (Konsolidasi)';

        // Jika mode harian untuk unit mingguan (TPS), return kosong
        if ($range === null) {
            return [
                'unit'            => $namaUnit,
                'kategoriRows'    => collect([]),
                'totalPendapatan' => 0,
                'kosong'          => true,
                'pesanKosong'     => 'Mode Harian tidak tersedia untuk unit TPS karena data diinput per minggu. Silakan pilih mode Mingguan atau Bulanan.',
            ];
        }

        // Ambil ID transaksi_harian yang sesuai filter
        $transaksiIds = $this->buildTransaksiHarianQuery($unit, $range)->pluck('id');

        if ($transaksiIds->isEmpty()) {
            return [
                'unit'            => $namaUnit,
                'kategoriRows'    => collect([]),
                'totalPendapatan' => 0,
                'kosong'          => true,
                'pesanKosong'     => 'Tidak ada data pemasukan pada periode ini.',
            ];
        }

        // Agregasi detail per kategori — 1 query tunggal
        $aggrRows = TransaksiDetail::whereIn('transaksi_harian_id', $transaksiIds)
            ->select(
                'kategori_transaksi_id',
                DB::raw('SUM(qty) as total_qty'),
                DB::raw('MAX(harga_satuan) as harga_satuan'),   // harga_satuan sama sepanjang periode (cukup MAX)
                DB::raw('SUM(subtotal) as total_subtotal')
            )
            ->groupBy('kategori_transaksi_id')
            ->with('kategoriTransaksi.unitWisata')
            ->get();

        // Susun kategoriRows sesuai format yang diinginkan
        $kategoriRows = $aggrRows->map(function ($row) {
            $kat = $row->kategoriTransaksi;

            return [
                'kategori_id'   => $kat->id,
                'kategori'      => $kat->nama,
                'unit_nama'     => $kat->unitWisata?->nama ?? '-',
                'tipe'          => $kat->tipe->value,  // harga_x_qty | flat | bebas | tahunan
                'harga_satuan'  => $kat->tipe->value === 'harga_x_qty' ? (float) $row->harga_satuan : null,
                'jumlah_qty'    => $kat->tipe->value === 'harga_x_qty' ? (int) $row->total_qty : null,
                'subtotal'      => (float) $row->total_subtotal,
            ];
        })->sortBy('kategori')->values();

        $totalPendapatan = $kategoriRows->sum('subtotal');

        return [
            'unit'            => $namaUnit,
            'kategoriRows'    => $kategoriRows,
            'totalPendapatan' => $totalPendapatan,
            'kosong'          => $kategoriRows->isEmpty(),
            'pesanKosong'     => 'Tidak ada data pemasukan pada periode ini.',
        ];
    }

    public function updatedMode(): void
    {
        // Jika user memilih mode harian padahal unit-nya mingguan, otomatis pindah ke mingguan
        $unit = $this->selectedUnit;
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function updatedUnitId(): void
    {
        // Kalau user ganti ke TPS dan mode masih harian → otomatis pindah ke mingguan
        $unit = UnitWisata::find($this->unit_id);
        if ($this->mode === 'harian' && $this->isUnitMingguan($unit)) {
            $this->mode = 'mingguan';
        }
    }

    public function render()
    {
        return view('livewire.laporan-pendapatan.pendapatan');
    }
}
