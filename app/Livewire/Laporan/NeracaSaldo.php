<?php

namespace App\Livewire\Laporan;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        $this->periode = Carbon::now()->format('Y-m');
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
    public function units(): \Illuminate\Database\Eloquent\Collection
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
        $neracaData = collect();
        $totalDebit = 0;
        $totalKredit = 0;

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

                if ($sumDebit == 0 && $sumKredit == 0) {
                    continue; // Skip accounts with zero balance
                }

                $normalBalance = $this->getNormalBalanceType($akun->tipe);
                $saldoAkhir = 0;
                $posisiDebit = 0;
                $posisiKredit = 0;

                if ($normalBalance === 'debit') {
                    $saldoAkhir = $sumDebit - $sumKredit;
                    if ($saldoAkhir > 0) {
                        $posisiDebit = $saldoAkhir;
                    } elseif ($saldoAkhir < 0) {
                        $posisiKredit = abs($saldoAkhir);
                    }
                } else {
                    $saldoAkhir = $sumKredit - $sumDebit;
                    if ($saldoAkhir > 0) {
                        $posisiKredit = $saldoAkhir;
                    } elseif ($saldoAkhir < 0) {
                        $posisiDebit = abs($saldoAkhir);
                    }
                }

                if ($posisiDebit > 0 || $posisiKredit > 0) {
                    $neracaData->push((object)[
                        'kode' => $akun->kode,
                        'nama' => $akun->nama,
                        'debit' => $posisiDebit,
                        'kredit' => $posisiKredit,
                    ]);
                    
                    $totalDebit += $posisiDebit;
                    $totalKredit += $posisiKredit;
                }
            }
        }

        return compact('neracaData', 'totalDebit', 'totalKredit');
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
        $jabatan = match(true) {
            Auth::user()->hasRole('direktur_bumdes') => 'Direktur',
            Auth::user()->hasRole('sekretaris') => 'Sekretaris',
            Auth::user()->hasRole('bendahara') => 'Bendahara',
            Auth::user()->hasRole('kepala_unit') => 'Kepala Unit'.($unit ? ' '.$unit->nama : ''),
            default => '',
        };

        $pdf = Pdf::loadView('pdf.neraca-saldo', array_merge($data, compact('periodeLabel', 'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'penandatangan', 'jabatan')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->nama) : 'Konsolidasi';
        $filename = 'NeracaSaldo_'.$unitSlug.'_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.laporan.neraca-saldo');
    }
}
