<?php

namespace App\Livewire\KepalaUnit;

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Models\KategoriHargaRiwayat;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\TransaksiDetail;
use App\Models\User;
use App\Notifications\HargaKategoriDiubah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Kelola Pendapatan')]
class KelolaPendapatan extends Component
{
    public int $unitId;

    public string $unitNama;

    /** @var array<int, float|string> harga per kategori (untuk edit harga existing) */
    public array $prices = [];

    // ─── Form tambah kategori baru ────────────────────────────────────────
    public string $namaKategori = '';

    public string $tipeKategori = 'harga_x_qty';

    public string $hargaKategori = '';

    public ?int $kodeAkunKategoriId = null;

    public bool $showTambahForm = false;

    // ─── Form edit kategori ────────────────────────────────────────────────
    public bool $showEditModal = false;

    public ?int $editId = null;

    public string $editNamaKategori = '';

    public string $editTipeKategori = 'harga_x_qty';

    public ?int $editKodeAkunKategoriId = null;

    // ─── Form hapus kategori ───────────────────────────────────────────────
    public bool $showDeleteModal = false;

    public ?int $deleteId = null;

    public string $deleteNamaKategori = '';

    // ─────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        $user = Auth::user();

        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unitNama = $user->unitWisata->nama;

        $this->loadPrices();
    }

    private function loadPrices(): void
    {
        $categories = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('jenis', JenisTransaksi::Pemasukan)
            ->where('tipe', '!=', TipeKategori::Bebas->value)
            ->get();

        foreach ($categories as $category) {
            $this->prices[$category->id] = $category->hargaSaat(now());
        }
    }

    // ─── Update harga kategori existing ──────────────────────────────────

    public function updateHarga(int $categoryId): void
    {
        $category = KategoriTransaksi::where('id', $categoryId)
            ->where('unit_wisata_id', $this->unitId)
            ->firstOrFail();

        $newPrice = (float) str_replace(['Rp', '.', ',', ' '], '', $this->prices[$categoryId] ?? '0');

        if ($newPrice <= 0) {
            $this->addError('prices.'.$categoryId, 'Harga harus berupa angka positif.');

            return;
        }

        $oldPrice = $category->hargaSaat(now());

        if ($newPrice === $oldPrice) {
            $this->addError('prices.'.$categoryId, 'Harga tidak berubah.');

            return;
        }

        KategoriHargaRiwayat::create([
            'kategori_transaksi_id' => $category->id,
            'harga' => $newPrice,
            'berlaku_dari' => now(),
        ]);

        $message = "Kepala Unit {$this->unitNama} mengubah harga {$category->nama} dari Rp "
            .number_format($oldPrice, 0, ',', '.').' menjadi Rp '.number_format($newPrice, 0, ',', '.');

        $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
        foreach ($recipients as $recipient) {
            $recipient->notify(new HargaKategoriDiubah($message));
        }

        session()->flash('success_'.$categoryId, 'Harga berhasil diperbarui!');
        $this->prices[$categoryId] = $newPrice;
        $this->resetErrorBag();
    }

    // ─── Tambah kategori baru ─────────────────────────────────────────────

    public function updatedTipeKategori(): void
    {
        // Reset the price when switching to the free type
        if ($this->tipeKategori === 'bebas') {
            $this->hargaKategori = '';
        }
    }

    public function tambahKategori(): void
    {
        $rules = [
            'namaKategori' => 'required|string|max:100',
            'tipeKategori' => 'required|in:harga_x_qty,tahunan,bebas',
            'kodeAkunKategoriId' => 'required|exists:kode_akun,id',
        ];

        if (in_array($this->tipeKategori, ['harga_x_qty', 'tahunan'])) {
            $rules['hargaKategori'] = 'required|numeric|min:0';
        }

        $this->validate($rules, [
            'namaKategori.required' => 'Nama kategori wajib diisi.',
            'tipeKategori.required' => 'Tipe kategori wajib dipilih.',
            'kodeAkunKategoriId.required' => 'Pilih akun pendapatan untuk kategori ini.',
            'hargaKategori.required' => 'Harga atau nominal wajib diisi untuk tipe ini.',
            'hargaKategori.min' => 'Harga tidak boleh negatif.',
        ]);

        // Check for duplicate names within the same unit
        $duplikat = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('nama', $this->namaKategori)
            ->exists();

        if ($duplikat) {
            $this->addError('namaKategori', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        DB::transaction(function () {
            $kategori = KategoriTransaksi::create([
                'unit_wisata_id' => $this->unitId,
                'kode_akun_id' => $this->kodeAkunKategoriId,
                'nama' => $this->namaKategori,
                'tipe' => TipeKategori::from($this->tipeKategori),
                'jenis' => JenisTransaksi::Pemasukan,
            ]);

            // Store the initial price unless the type is free
            if (in_array($this->tipeKategori, ['harga_x_qty', 'tahunan'])) {
                KategoriHargaRiwayat::create([
                    'kategori_transaksi_id' => $kategori->id,
                    'harga' => (float) $this->hargaKategori,
                    'berlaku_dari' => now()->startOfDay(),
                ]);

                // Append to the prices array so it shows up in the price-edit list immediately
                $this->prices[$kategori->id] = (float) $this->hargaKategori;
            }

            // Notify the treasurer & director
            $akun = KodeAkun::find($this->kodeAkunKategoriId);
            $message = "Kepala Unit {$this->unitNama} menambahkan kategori pendapatan baru: "
                ."\"{$this->namaKategori}\" (Tipe: {$this->tipeKategori}, Akun: {$akun?->kode} {$akun?->nama})";

            $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new HargaKategoriDiubah($message));
            }
        });

        \Flux::toast(variant: 'success', text: "Kategori \"{$this->namaKategori}\" berhasil ditambahkan!");

        // Reset the form
        $this->reset(['namaKategori', 'hargaKategori', 'kodeAkunKategoriId']);
        $this->tipeKategori = 'harga_x_qty';
        $this->showTambahForm = false;
    }

    // ─── Edit Kategori ───────────────────────────────────────────────────

    public function editKategori(int $id): void
    {
        $category = KategoriTransaksi::where('id', $id)
            ->where('unit_wisata_id', $this->unitId)
            ->firstOrFail();

        $this->editId = $category->id;
        $this->editNamaKategori = $category->nama;
        $this->editTipeKategori = $category->tipe->value;
        $this->editKodeAkunKategoriId = $category->kode_akun_id;

        $this->showEditModal = true;
    }

    public function simpanEditKategori(): void
    {
        $this->validate([
            'editNamaKategori' => 'required|string|max:100',
            'editTipeKategori' => 'required|in:harga_x_qty,tahunan,bebas',
            'editKodeAkunKategoriId' => 'required|exists:kode_akun,id',
        ], [
            'editNamaKategori.required' => 'Nama kategori wajib diisi.',
            'editTipeKategori.required' => 'Tipe kategori wajib dipilih.',
            'editKodeAkunKategoriId.required' => 'Pilih akun pendapatan.',
        ]);

        $duplikat = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('nama', $this->editNamaKategori)
            ->where('id', '!=', $this->editId)
            ->exists();

        if ($duplikat) {
            $this->addError('editNamaKategori', 'Nama kategori sudah ada di unit ini.');

            return;
        }

        $category = KategoriTransaksi::findOrFail($this->editId);

        $oldName = $category->nama;

        $category->update([
            'nama' => $this->editNamaKategori,
            'tipe' => TipeKategori::from($this->editTipeKategori),
            'kode_akun_id' => $this->editKodeAkunKategoriId,
        ]);

        // Notify about the change when the name changes
        if ($oldName !== $this->editNamaKategori) {
            $message = "Kepala Unit {$this->unitNama} mengubah kategori \"{$oldName}\" menjadi \"{$this->editNamaKategori}\".";
            $recipients = User::role(['bendahara', 'direktur_bumdes'])->get();
            foreach ($recipients as $recipient) {
                $recipient->notify(new HargaKategoriDiubah($message));
            }
        }

        \Flux::toast(variant: 'success', text: 'Kategori berhasil diperbarui!');

        $this->showEditModal = false;
        $this->reset(['editId', 'editNamaKategori', 'editTipeKategori', 'editKodeAkunKategoriId']);
        $this->loadPrices();
    }

    public function confirmDelete(int $id): void
    {
        $category = KategoriTransaksi::where('id', $id)
            ->where('unit_wisata_id', $this->unitId)
            ->firstOrFail();

        $this->deleteId = $category->id;
        $this->deleteNamaKategori = $category->nama;
        $this->showDeleteModal = true;
    }

    public function hapusKategori(): void
    {
        if (! $this->deleteId) {
            return;
        }

        $category = KategoriTransaksi::where('id', $this->deleteId)
            ->where('unit_wisata_id', $this->unitId)
            ->firstOrFail();

        // Check usage in transaction details (undeletable when referenced, to prevent data loss)
        $terpakai = TransaksiDetail::where('kategori_transaksi_id', $this->deleteId)->exists();

        if ($terpakai) {
            \Flux::toast(variant: 'danger', text: 'Kategori tidak bisa dihapus karena sudah dipakai dalam riwayat transaksi!');
            $this->showDeleteModal = false;

            return;
        }

        $id = $this->deleteId;
        $category->delete();
        \Flux::toast(variant: 'success', text: 'Kategori berhasil dihapus!');

        unset($this->prices[$id]);
        $this->loadPrices();

        $this->showDeleteModal = false;
        $this->deleteId = null;
        $this->deleteNamaKategori = '';
    }

    // ─── Render ──────────────────────────────────────────────────────────

    public function render()
    {
        $categories = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('jenis', JenisTransaksi::Pemasukan)
            ->orderBy('nama')
            ->get();

        $akunPendapatan = KodeAkun::where('tipe', 'pendapatan')
            ->orderBy('kode')
            ->get();

        return view('livewire.kepala-unit.kelola-pendapatan', [
            'categories' => $categories,
            'akunPendapatan' => $akunPendapatan,
        ]);
    }
}
