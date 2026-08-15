<?php

namespace App\Livewire\DirekturBumdes;

use App\Models\JurnalUmum;
use App\Traits\ExportsJurnalPdf;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TransactionList extends Component
{
    use ExportsJurnalPdf;

    public ?int $deleteId = null;

    public bool $showDeleteModal = false;

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function executeDelete(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $jurnal = JurnalUmum::find($this->deleteId);
        if ($jurnal) {
            DB::transaction(function () use ($jurnal) {
                // Menghapus semua baris jurnal yang memiliki nomor_bukti yang sama
                // karena satu transaksi pemasukan terdiri dari debet dan kredit
                JurnalUmum::where('nomor_bukti', $jurnal->nomor_bukti)->delete();
            });
            \Flux::toast(variant: 'success', text: 'Satu set jurnal (debet & kredit) berhasil dihapus.');
        }

        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function render()
    {
        $journals = JurnalUmum::with(['unitWisata', 'kodeAkun'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('nomor_bukti', 'desc')
            ->orderBy('id', 'asc')
            ->get()
            ->groupBy('nomor_bukti');

        return view('livewire.direktur-bumdes.transaction-list', [
            'journals' => $journals,
        ])->layout('layouts.app', ['title' => 'Kelola Jurnal Direktur BUMDes']);
    }
}
