<?php

namespace App\Livewire\KepalaUnit;

use App\Models\KategoriHargaRiwayat;
use App\Models\KategoriTransaksi;
use App\Models\User;
use App\Notifications\HargaKategoriDiubah;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Harga Kategori')]
class KelolaHarga extends Component
{
    public $unitId;

    public $unitNama;

    public $prices = [];

    public function mount()
    {
        $user = Auth::user();
        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unitNama = $user->unitWisata->nama;

        $categories = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('tipe', '!=', 'bebas')
            ->get();

        foreach ($categories as $category) {
            $this->prices[$category->id] = $category->hargaSaat(now());
        }
    }

    public function updateHarga($categoryId)
    {
        $category = KategoriTransaksi::where('id', $categoryId)
            ->where('unit_wisata_id', $this->unitId)
            ->firstOrFail();

        $newPrice = str_replace(['Rp', '.', ',', ' '], '', $this->prices[$categoryId] ?? '0');

        if (! is_numeric($newPrice) || $newPrice <= 0) {
            $this->addError('prices.'.$categoryId, 'Harga harus berupa angka positif.');

            return;
        }

        $oldPrice = $category->hargaSaat(now());

        if ((float) $newPrice === (float) $oldPrice) {
            $this->addError('prices.'.$categoryId, 'Harga tidak berubah.');

            return;
        }

        // Simpan harga baru
        KategoriHargaRiwayat::create([
            'kategori_transaksi_id' => $category->id,
            'harga' => $newPrice,
            'berlaku_dari' => now(),
        ]);

        // Kirim Notifikasi
        $message = "Kepala Unit {$this->unitNama} mengubah harga {$category->nama} dari Rp ".number_format($oldPrice, 0, ',', '.').' menjadi Rp '.number_format($newPrice, 0, ',', '.');

        $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new HargaKategoriDiubah($message));
        }

        session()->flash('success_'.$categoryId, 'Harga berhasil diperbarui!');
        $this->prices[$categoryId] = $newPrice;
        $this->resetErrorBag();
    }

    public function render()
    {
        $categories = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('tipe', '!=', 'bebas')
            ->get();

        return view('livewire.kepala-unit.kelola-harga', [
            'categories' => $categories,
        ]);
    }
}
