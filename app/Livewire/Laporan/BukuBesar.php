<?php

namespace App\Livewire\Laporan;

use App\Models\JurnalUmum;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Laporan Buku Besar')]
class BukuBesar extends Component
{
    /** null = konsolidasi semua unit */
    public ?int $unit_id = null;

    /** Format Y-m */
    public string $periode = '';

    public ?int $kode_akun_id = null;

    public function mount(): void
    {
        $user = Auth::user();

        // Kepala unit: kunci ke unit sendiri
        if ($user->hasRole('kepala_unit')) {
            $this->unit_id = $user->unit_wisata_id;
        }

        $this->periode = Carbon::now()->format('Y-m');
        
        $firstAkun = KodeAkun::where('tipe', '!=', 'header')->orderBy('kode')->first();
        if ($firstAkun) {
            $this->kode_akun_id = $firstAkun->id;
        }
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
    public function akuns(): \Illuminate\Database\Eloquent\Collection
    {
        return KodeAkun::where('tipe', '!=', 'header')->orderBy('kode')->get();
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
        $transactions = collect();
        $saldoAwal = 0;
        $selectedAkun = null;
        $normalBalance = 'debit';
        $totalDebit = 0;
        $totalKredit = 0;

        if ($this->kode_akun_id && $this->periode) {
            $selectedAkun = KodeAkun::find($this->kode_akun_id);
            
            if ($selectedAkun) {
                $normalBalance = $this->getNormalBalanceType($selectedAkun->tipe);
                [$startDate, $endDate] = $this->periodeRange();

                // Calculate Saldo Awal (before start date)
                $queryAwal = JurnalUmum::where('kode_akun_id', $this->kode_akun_id)
                                      ->whereDate('tanggal', '<', $startDate->format('Y-m-d'));
                
                if ($this->unit_id) {
                    $queryAwal->where('unit_wisata_id', $this->unit_id);
                }

                $sumDebitAwal = (clone $queryAwal)->sum('debet');
                $sumKreditAwal = (clone $queryAwal)->sum('kredit');

                if ($normalBalance === 'debit') {
                    $saldoAwal = $sumDebitAwal - $sumKreditAwal;
                } else {
                    $saldoAwal = $sumKreditAwal - $sumDebitAwal;
                }

                // Get current transactions
                $queryCurrent = JurnalUmum::with('unitWisata')
                                        ->where('kode_akun_id', $this->kode_akun_id)
                                        ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                                        ->orderBy('tanggal', 'asc')
                                        ->orderBy('id', 'asc');
                
                if ($this->unit_id) {
                    $queryCurrent->where('unit_wisata_id', $this->unit_id);
                }

                $transactions = $queryCurrent->get();
                $totalDebit = $transactions->sum('debet');
                $totalKredit = $transactions->sum('kredit');
            }
        }

        return compact('transactions', 'saldoAwal', 'selectedAkun', 'normalBalance', 'totalDebit', 'totalKredit');
    }

    public function exportPdf()
    {
        if (! $this->canPrint) {
            abort(403);
        }

        $data = $this->reportData;
        if (!$data['selectedAkun']) {
            abort(404, 'Kode Akun tidak ditemukan.');
        }

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

        $pdf = Pdf::loadView('pdf.buku-besar', array_merge($data, compact('periodeLabel', 'namaEntitas', 'tanggalCetak', 'tanggalTtd', 'penandatangan', 'jabatan')))
            ->setPaper('a4', 'portrait');

        $unitSlug = $unit ? str_replace(' ', '_', $unit->nama) : 'Konsolidasi';
        $filename = 'BukuBesar_'.$unitSlug.'_'.$data['selectedAkun']->kode.'_'.str_replace(' ', '_', $periodeLabel).'.pdf';

        return response()->streamDownload(fn () => print($pdf->output()), $filename);
    }

    public function render()
    {
        return view('livewire.laporan.buku-besar');
    }
}
