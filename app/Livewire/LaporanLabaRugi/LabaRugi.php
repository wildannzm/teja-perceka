<?php

namespace App\Livewire\LaporanLabaRugi;

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
#[Title('Laporan Laba Rugi')]
class LabaRugi extends Component
{
    public $unit_id = null;

    /** 'bulanan' | 'semester' | 'tahunan' */
    public string $mode = 'bulanan';

    /** Format Y-m untuk bulanan, Y untuk tahunan */
    public string $periode = '';

    public string $semester = '1';

    public string $semesterTahun = '';

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        // Default periode ke bulan/tahun berjalan
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

    // ─── Inline Edit ─────────────────────────────────────────────────────

    public bool $isEditing = false;

    public array $editValues = [];

    public function startEditing(): void
    {
        if (! Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes'])) {
            abort(403);
        }

        $this->isEditing = true;

        // Populate editValues with current totals
        $data = $this->reportData;
        foreach (['pendapatanRows', 'hppRows', 'bebanRows', 'pendapatanLainRows', 'bebanLainRows'] as $group) {
            foreach ($data[$group] as $row) {
                $this->editValues[$row->id] = $row->jumlah;
            }
        }
    }

    public function cancelEditing(): void
    {
        $this->isEditing = false;
        $this->editValues = [];
    }

    public function saveAdjustments(): void
    {
        if (! Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes'])) {
            abort(403);
        }

        $data = $this->reportData;
        $allOriginalRows = collect();
        foreach (['pendapatanRows', 'hppRows', 'bebanRows', 'pendapatanLainRows', 'bebanLainRows'] as $group) {
            foreach ($data[$group] as $row) {
                // Attach the group name so we know the arahNormal
                $row->groupName = $group;
                $allOriginalRows->push($row);
            }
        }

        [$start, $end] = $this->periodeRange();
        $batchTime = time();

        foreach ($this->editValues as $akunId => $newValue) {
            $newValue = (float) $newValue;
            $originalRow = $allOriginalRows->firstWhere('id', $akunId);

            if ($originalRow) {
                $selisih = $newValue - $originalRow->jumlah;

                if ($selisih != 0) {
                    // Determine normal balance based on group
                    $arahNormal = in_array($originalRow->groupName, ['pendapatanRows', 'pendapatanLainRows']) ? 'kredit' : 'debet';

                    $debet = 0;
                    $kredit = 0;

                    if ($arahNormal === 'kredit') {
                        if ($selisih > 0) {
                            $kredit = abs($selisih);
                        } else {
                            $debet = abs($selisih);
                        }
                    } else { // arah normal debet
                        if ($selisih > 0) {
                            $debet = abs($selisih);
                        } else {
                            $kredit = abs($selisih);
                        }
                    }

                    JurnalUmum::create([
                        'nomor_bukti' => 'ADJ-'.$batchTime.'-'.$akunId,
                        'tanggal' => $end->format('Y-m-d'),
                        'keterangan' => 'Penyesuaian Manual Laba Rugi',
                        'kode_akun_id' => $akunId,
                        'debet' => $debet,
                        'kredit' => $kredit,
                        'unit_wisata_id' => $this->unit_id,
                    ]);
                }
            }
        }

        $this->isEditing = false;
        $this->editValues = [];
        unset($this->reportData);
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

    private function buildQuery()
    {
        [$start, $end] = $this->periodeRange();
        $q = JurnalUmum::query()->whereBetween('tanggal', [$start->format('Y-m-d'), $end->format('Y-m-d')]);
        if ($this->unit_id) {
            $q->where('unit_wisata_id', $this->unit_id);
        }

        return $q;
    }

    // ─── Computed ────────────────────────────────────────────────────────

    #[Computed]
    public function units(): \Illuminate\Database\Eloquent\Collection
    {
        return UnitWisata::orderBy('nama')->get();
    }

    private function akunDenganSaldo(string $tipe, string $arahNormal): Collection
    {
        $base = $this->buildQuery();

        $akunList = KodeAkun::where('tipe', $tipe)
            ->orderBy('urutan')
            ->get();

        $akunIds = $akunList->pluck('id');

        if ($akunIds->isEmpty()) {
            return collect();
        }

        $sums = (clone $base)
            ->whereIn('kode_akun_id', $akunIds)
            ->selectRaw('kode_akun_id, '.($arahNormal === 'kredit'
                ? 'SUM(kredit) - SUM(debet) as jumlah'
                : 'SUM(debet) - SUM(kredit) as jumlah'))
            ->groupBy('kode_akun_id')
            ->pluck('jumlah', 'kode_akun_id');

        return $akunList->map(function ($akun) use ($sums) {
            return (object) [
                'id' => $akun->id,
                'kode' => $akun->kode,
                'nama' => $akun->nama,
                'jumlah' => $sums[$akun->id] ?? 0,
            ];
        });
    }

    #[Computed]
    public function reportData(): array
    {
        $pendapatanRows = $this->akunDenganSaldo('pendapatan', 'kredit');
        $totalPendapatan = $pendapatanRows->sum('jumlah');

        $hppRows = $this->akunDenganSaldo('hpp', 'debet');
        $totalHpp = $hppRows->sum('jumlah');

        $labaKotor = $totalPendapatan - $totalHpp;

        $bebanRows = $this->akunDenganSaldo('beban', 'debet');
        $totalBeban = $bebanRows->sum('jumlah');

        $pendapatanLainRows = $this->akunDenganSaldo('pendapatan_lain', 'kredit');
        $totalPendapatanLain = $pendapatanLainRows->sum('jumlah');

        $bebanLainRows = $this->akunDenganSaldo('beban_lain', 'debet');
        $totalBebanLain = $bebanLainRows->sum('jumlah');

        $labaBersih = $labaKotor - $totalBeban + $totalPendapatanLain - $totalBebanLain;

        return compact(
            'pendapatanRows', 'totalPendapatan',
            'hppRows', 'totalHpp', 'labaKotor',
            'bebanRows', 'totalBeban',
            'pendapatanLainRows', 'totalPendapatanLain',
            'bebanLainRows', 'totalBebanLain',
            'labaBersih'
        );
    }

    #[Computed]
    public function isKepalaUnit(): bool
    {
        return Auth::user()->hasRole('kepala_unit');
    }

    #[Computed]
    public function canPrint(): bool
    {
        return Auth::user()->hasAnyRole(['kepala_unit', 'sekretaris', 'bendahara', 'direktur_bumdes']);
    }

    #[Computed]
    public function selectedUnit(): ?UnitWisata
    {
        return $this->unit_id ? UnitWisata::find($this->unit_id) : null;
    }

    // ─── Export PDF ──────────────────────────────────────────────────────

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
        $periodeLabel = $this->periodeLabel();
        $unit = $this->selectedUnit;
        $namaEntitas = $unit ? 'WISATA '.strtoupper($unit->nama) : 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodeRange();
        $tanggalCetak = strtoupper($end->translatedFormat('d F Y'));
        $tanggalTtd = $end->translatedFormat('F Y');

        $penandatangan = Auth::user()->name;
        $jabatan = match (true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->nama : ''),
            default => '',
        };

        ini_set('memory_limit', '-1');
        set_time_limit(300);

        $pdf = Pdf::loadView('pdf.laba-rugi', array_merge($data, compact('periodeLabel', 'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'penandatangan', 'jabatan')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->nama) : 'Konsolidasi';
        $filename = 'LabaRugi_'.$unitSlug.'_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
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
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->nama : ''),
            default => '',
        };

        return view('livewire.laporan-laba-rugi.laba-rugi', compact(
            'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'periodeLabel', 'penandatangan', 'jabatan'
        ));
    }
}
