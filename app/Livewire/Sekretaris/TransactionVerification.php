<?php

namespace App\Livewire\Sekretaris;

use Livewire\Component;
use App\Models\Unit;
use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalDetail;
use Illuminate\Support\Facades\DB;

class TransactionVerification extends Component
{
    public $unit_id = '';
    public $date = '';
    public $journal_id = null;
    
    // Dynamic Form Data
    public $form_data = [];

    // Summary
    public $total_pemasukan = 0;
    public $total_pengeluaran = 0;
    public $selisih = 0;

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->unit_id = Unit::first()->id ?? null;
    }

    public function updatedUnitId()
    {
        $this->loadData();
    }

    public function updatedDate()
    {
        $this->loadData();
    }

    public function loadData()
    {
        if (!$this->unit_id || !$this->date) return;

        // Cari jurnal untuk unit ini pada tanggal ini (khusus pendapatan/pengeluaran pariwisata yang disubmit unit)
        // Kita asumsikan description awal mengandung kata 'Penerimaan' atau kita ambil dari metadata
        $journal = Journal::where('unit_id', $this->unit_id)
            ->whereDate('date', $this->date)
            ->whereNotNull('metadata')
            ->first();

        if ($journal) {
            $this->journal_id = $journal->id;
            $this->form_data = $journal->metadata ?? [];
        } else {
            $this->journal_id = null;
            $this->form_data = [];
        }

        $this->calculateTotals();
    }

    public function updatedFormData()
    {
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        $unit = Unit::find($this->unit_id);
        if (!$unit) return;

        $this->total_pemasukan = 0;
        $this->total_pengeluaran = 0;

        $name = strtolower($unit->name);

        if (str_contains($name, 'sawah bengkok')) {
            $this->total_pemasukan = 
                (($this->form_data['tiket_dewasa'] ?? 0) * 10000) +
                (($this->form_data['tiket_anak'] ?? 0) * 5000) +
                (($this->form_data['parkir_mobil'] ?? 0) * 5000) +
                (($this->form_data['parkir_motor'] ?? 0) * 2000) +
                (($this->form_data['sewa_ban'] ?? 0) * 10000) +
                (($this->form_data['sewa_plampung'] ?? 0) * 10000) +
                (($this->form_data['kios'] ?? 0) * 150000) +
                (($this->form_data['kaki_lima'] ?? 0) * 20000);
        } elseif (str_contains($name, 'situ ciranca')) {
            $this->total_pemasukan = 
                (($this->form_data['tiket_dewasa'] ?? 0) * 10000) +
                (($this->form_data['tiket_anak'] ?? 0) * 5000) +
                (($this->form_data['parkir_mobil'] ?? 0) * 5000) +
                (($this->form_data['parkir_motor'] ?? 0) * 3000) +
                (($this->form_data['sewa_ban'] ?? 0) * 10000) +
                (($this->form_data['sewa_plampung'] ?? 0) * 10000) +
                (($this->form_data['sewa_bebek'] ?? 0) * 10000);
        } elseif (str_contains($name, 'bukit sampora')) {
            $this->total_pemasukan = 
                (($this->form_data['tiket'] ?? 0) * 15000);
        } elseif (str_contains($name, 'buper')) {
            $this->total_pemasukan = 
                (($this->form_data['tiket'] ?? 0) * 20000);
        } elseif (str_contains($name, 'tps')) {
            $this->total_pemasukan = 
                (($this->form_data['sampah_rumah'] ?? 0) * 15000) +
                (($this->form_data['sampah_kegiatan'] ?? 0) * 100000) +
                ((float)($this->form_data['sampah_wisata_nominal'] ?? 0));
        }

        // Pengeluaran Umum
        $this->total_pengeluaran = 
            (float)($this->form_data['setoran_parkir'] ?? 0) + 
            (float)($this->form_data['setoran_lahan'] ?? 0) + 
            (float)($this->form_data['bagi_hasil'] ?? 0) + 
            (float)($this->form_data['nota_warung'] ?? 0) + 
            (float)($this->form_data['pengeluaran_lain'] ?? 0);

        $this->selisih = $this->total_pemasukan - $this->total_pengeluaran;
    }

    public function simpanVerifikasi()
    {
        if (!$this->journal_id) {
            \Flux::toast(
                variant: 'danger',
                text: 'Belum ada data jurnal dari Unit untuk diverifikasi pada tanggal ini.'
            );
            return;
        }

        $this->calculateTotals();

        DB::transaction(function () {
            // Update Journal Metadata
            $journal = Journal::find($this->journal_id);
            $journal->update([
                'metadata' => $this->form_data,
                'description' => 'Laporan Harian (Telah Diverifikasi Sekretaris)'
            ]);

            // Hapus Jurnal Detail Lama untuk Journal ini (Biar bersih dan di-recreate)
            JournalDetail::where('journal_id', $journal->id)->delete();

            // Re-create Jurnal Detail
            $kasAccount = Account::where('code', '1-1100')->first();
            $pendapatanAccount = Account::where('code', '4-2000')->first();
            $biayaAccount = Account::where('code', '6-0023')->first();

            // 1. Pemasukan
            if ($this->total_pemasukan > 0) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $kasAccount->id,
                    'debit' => $this->total_pemasukan,
                    'credit' => 0
                ]);
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $pendapatanAccount->id,
                    'debit' => 0,
                    'credit' => $this->total_pemasukan
                ]);
            }

            // 2. Pengeluaran
            if ($this->total_pengeluaran > 0) {
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $biayaAccount->id,
                    'debit' => $this->total_pengeluaran,
                    'credit' => 0
                ]);
                JournalDetail::create([
                    'journal_id' => $journal->id,
                    'account_id' => $kasAccount->id,
                    'debit' => 0,
                    'credit' => $this->total_pengeluaran
                ]);
            }
        });

        \Flux::toast(
            variant: 'success',
            text: 'Verifikasi berhasil disimpan dan nilai pembukuan telah disesuaikan!'
        );
        $this->loadData();
    }

    public function render()
    {
        $selectedUnit = Unit::find($this->unit_id);
        
        return view('livewire.sekretaris.transaction-verification', [
            'units' => Unit::all(),
            'selectedUnit' => $selectedUnit
        ])->layout('layouts.app', ['title' => 'Verifikasi Harian']);
    }
}
