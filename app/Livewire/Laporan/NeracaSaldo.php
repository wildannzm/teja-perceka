<?php

namespace App\Livewire\Laporan;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Neraca Saldo')]
class NeracaSaldo extends Component
{
    /** null = konsolidasi semua unit */
    public ?int $unit_id = null;

    /** Format Y-m */
    public string $periode = '';

    public bool $isEditing = false;

    public array $editValues = [];

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        $this->periode = Carbon::now()->format('Y-m');
    }

    public function startEditing(): void
    {
        $this->isEditing = true;
        $this->editValues = [];
        $data = $this->reportData;
        foreach (['aktivaLancar', 'aktivaTetap', 'kewajibanPendek', 'kewajibanPanjang', 'ekuitas'] as $key) {
            foreach ($data[$key] as $row) {
                $this->editValues[$row->id] = $row->saldo;
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
        $data = $this->reportData;
        $allOriginalRows = collect();

        foreach (['aktivaLancar', 'aktivaTetap', 'kewajibanPendek', 'kewajibanPanjang', 'ekuitas'] as $group) {
            foreach ($data[$group] as $row) {
                $allOriginalRows->push($row);
            }
        }

        [$start, $end] = $this->periodeRange();
        $batchTime = time();

        foreach ($this->editValues as $akunId => $newValue) {
            $newValue = (float) $newValue;
            $originalRow = $allOriginalRows->firstWhere('id', $akunId);

            if ($originalRow) {
                $selisih = $newValue - $originalRow->saldo;

                if ($selisih != 0) {
                    $akun = KodeAkun::find($akunId);
                    if (! $akun) {
                        continue;
                    }

                    $arahNormal = $this->getNormalBalanceType($akun->tipe);

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
                        'keterangan' => 'Penyesuaian Manual Neraca Saldo',
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

    private function getNormalBalanceType(string $tipe): string
    {
        $tipe = strtolower($tipe);
        if (in_array($tipe, ['aktiva', 'aset', 'beban', 'beban_lain', 'hpp'])) {
            return 'debit';
        }

        return 'kredit';
    }

    private function periodeRange(): array
    {
        $date = Carbon::parse($this->periode ?: Carbon::now()->format('Y-m'));

        return [
            $date->copy()->startOfMonth(),
            $date->copy()->endOfMonth(),
        ];
    }

    #[Computed]
    public function units(): Collection
    {
        return UnitWisata::orderBy('nama')->get();
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

    #[Computed]
    public function reportData(): array
    {
        $akuns = KodeAkun::where('tipe', '!=', 'header')->orderBy('kode')->get();

        $aktivaLancar = collect();
        $aktivaTetap = collect();
        $kewajibanPendek = collect();
        $kewajibanPanjang = collect();
        $ekuitas = collect();

        $totalAktivaLancar = 0;
        $totalAktivaTetap = 0;
        $totalKewajibanPendek = 0;
        $totalKewajibanPanjang = 0;
        $totalEkuitas = 0;

        $totalPendapatan = 0;
        $totalBeban = 0;

        if ($this->periode) {
            [$startDate, $endDate] = $this->periodeRange();

            $query = JurnalUmum::whereDate('tanggal', '<=', $endDate->format('Y-m-d'));

            if ($this->unit_id) {
                $query->where('unit_wisata_id', $this->unit_id);
            }

            $saldoPerAkun = $query->select(
                'kode_akun_id',
                DB::raw('SUM(debet) as total_debit'),
                DB::raw('SUM(kredit) as total_kredit')
            )
                ->groupBy('kode_akun_id')
                ->get()
                ->keyBy('kode_akun_id');

            foreach ($akuns as $akun) {
                $saldo = $saldoPerAkun->get($akun->id);
                $sumDebit = $saldo ? $saldo->total_debit : 0;
                $sumKredit = $saldo ? $saldo->total_kredit : 0;

                $normalBalance = $this->getNormalBalanceType($akun->tipe);
                $saldoAkhir = 0;

                if ($normalBalance === 'debit') {
                    $saldoAkhir = $sumDebit - $sumKredit;
                } else {
                    $saldoAkhir = $sumKredit - $sumDebit;
                }

                $prefix = substr($akun->kode, 0, 3);
                $kepala = substr($akun->kode, 0, 1);

                // Calculate Net Income (Laba Bersih) dynamically from nominal accounts
                if ($kepala === '4' || $kepala === '7') {
                    $totalPendapatan += $saldoAkhir; // Normal balance is kredit, so saldoAkhir is Kredit-Debit
                } elseif ($kepala === '5' || $kepala === '6') {
                    $totalBeban += $saldoAkhir; // Normal balance is debit, so saldoAkhir is Debit-Kredit
                }

                if ($saldoAkhir == 0 && $kepala !== '3' && $kepala !== '1' && $kepala !== '2') {
                    continue; // Skip zero balances unless we want to show them? Actually, let's include them if they are in the balance sheet structure but we can filter zero balance out in view or keep them as '-' like in excel.
                    // The Excel shows some '-' so we keep them, or we just keep all balance sheet accounts (1, 2, 3)
                }

                $item = (object) [
                    'id' => $akun->id,
                    'kode' => $akun->kode,
                    'nama' => $akun->nama,
                    'saldo' => $saldoAkhir,
                ];

                if ($prefix === '1-1') {
                    $aktivaLancar->push($item);
                    $totalAktivaLancar += $saldoAkhir;
                } elseif ($prefix === '1-2') {
                    $aktivaTetap->push($item);
                    $totalAktivaTetap += $saldoAkhir;
                } elseif ($prefix === '2-1') {
                    $kewajibanPendek->push($item);
                    $totalKewajibanPendek += $saldoAkhir;
                } elseif ($prefix === '2-2') {
                    $kewajibanPanjang->push($item);
                    $totalKewajibanPanjang += $saldoAkhir;
                } elseif ($kepala === '3') {
                    if ($akun->kode === '3-3000') {
                        // Skip injecting Laba Bersih here, we'll do it manually after the loop
                    } else {
                        $ekuitas->push($item);
                        $totalEkuitas += $saldoAkhir;
                    }
                }
            }

            // Inject Laba Bersih
            $labaBersih = $totalPendapatan - $totalBeban;

            $labaBersihAkun = KodeAkun::where('kode', '3-3000')->first();
            if ($labaBersihAkun) {
                $ekuitas->push((object) [
                    'id' => $labaBersihAkun->id,
                    'kode' => $labaBersihAkun->kode,
                    'nama' => 'LABA BERSIH', // Override name to match excel
                    'saldo' => $labaBersih,
                ]);
                $totalEkuitas += $labaBersih;
            }
        }

        $totalAktiva = $totalAktivaLancar + $totalAktivaTetap;
        $totalKewajiban = $totalKewajibanPendek + $totalKewajibanPanjang;
        $totalPasiva = $totalKewajiban + $totalEkuitas;

        return compact(
            'aktivaLancar', 'aktivaTetap', 'totalAktivaLancar', 'totalAktivaTetap', 'totalAktiva',
            'kewajibanPendek', 'kewajibanPanjang', 'totalKewajibanPendek', 'totalKewajibanPanjang', 'totalKewajiban',
            'ekuitas', 'totalEkuitas', 'totalPasiva', 'labaBersih'
        );
    }

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
        $unit = $this->selectedUnit;
        $namaEntitas = $unit ? 'WISATA '.strtoupper($unit->nama) : 'BUMDESA TEJA PERCEKA';

        [$start, $end] = $this->periodeRange();
        $tanggalCetak = strtoupper($end->translatedFormat('d F Y'));
        $tanggalTtd = $end->translatedFormat('F Y');
        $periodeLabel = Carbon::parse($this->periode ?: Carbon::now()->format('Y-m'))->translatedFormat('F Y');

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

        $pdf = Pdf::loadView('pdf.neraca-saldo', array_merge($data, compact('periodeLabel', 'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'penandatangan', 'jabatan')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->nama) : 'Konsolidasi';
        $filename = 'NeracaSaldo_'.$unitSlug.'_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print ($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.laporan.neraca-saldo');
    }
}
