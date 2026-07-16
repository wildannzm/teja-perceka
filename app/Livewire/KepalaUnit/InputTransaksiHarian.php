<?php

namespace App\Livewire\KepalaUnit;

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Models\KategoriTransaksi;
use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Component;

class InputTransaksiHarian extends Component
{
    #[Locked]
    public ?int $unitId = null;

    public ?UnitWisata $unit = null;

    public string $tanggal = '';

    public ?string $tanggalAkhir = null;

    public bool $isMingguan = false;

    // Array untuk menyimpan state input setiap kategori
    // Format: [kategori_id => ['qty' => value, 'nominal' => value, 'aktif' => boolean, 'subtotal' => value]]
    public array $inputs = [];

    public float $totalPemasukan = 0;

    public function mount()
    {
        $user = Auth::user();

        // Validasi akses hanya untuk role kepala_unit dan memiliki unit_wisata
        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit = UnitWisata::findOrFail($this->unitId);

        $today = Carbon::today();
        $this->isMingguan = $this->unit->frekuensi_input === 'mingguan';

        if ($this->isMingguan) {
            // Jika mingguan, ambil awal minggu (Senin) dan akhir minggu (Minggu) dari hari ini
            $this->tanggal = $today->copy()->startOfWeek()->format('Y-m-d');
            $this->tanggalAkhir = $today->copy()->endOfWeek()->format('Y-m-d');
        } else {
            $this->tanggal = $today->format('Y-m-d');
        }

        $this->initKategoriInputs();
    }

    public function updatedTanggal()
    {
        // Jika mingguan, tanggal bersifat readonly di UI.
        // Namun jika secara logika function ini terpanggil, kita snap kembali ke awal minggu yang dipilih.
        if ($this->isMingguan) {
            $date = Carbon::parse($this->tanggal);
            $this->tanggal = $date->copy()->startOfWeek()->format('Y-m-d');
            $this->tanggalAkhir = $date->copy()->endOfWeek()->format('Y-m-d');
        }

        // Hitung ulang semua subtotal karena harga mungkin berbeda di tanggal yang baru
        $this->calculateAllSubtotals();
    }

    public function updatedInputs()
    {
        $this->calculateAllSubtotals();
    }

    private function initKategoriInputs()
    {
        $kategoriList = KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('jenis', JenisTransaksi::Pemasukan)
            ->get();

        foreach ($kategoriList as $kategori) {
            $this->inputs[$kategori->id] = [
                'kategori' => $kategori,
                'tipe' => $kategori->tipe->value,
                'qty' => '',
                'nominal' => '',
                'aktif' => false,
                'subtotal' => 0,
            ];
        }
    }

    private function calculateAllSubtotals()
    {
        $total = 0;
        $date = Carbon::parse($this->tanggal);

        foreach ($this->inputs as $id => $input) {
            $kategori = $input['kategori'];
            $subtotal = 0;

            if ($input['tipe'] === TipeKategori::HargaXQty->value) {
                $qty = (int) ($input['qty'] ?: 0);
                if ($qty > 0) {
                    $hargaSatuan = $kategori->hargaSaat($date);
                    $subtotal = $qty * $hargaSatuan;
                }
            } elseif ($input['tipe'] === TipeKategori::Flat->value) {
                if ($input['aktif']) {
                    $subtotal = $kategori->hargaSaat($date);
                }
            } elseif ($input['tipe'] === TipeKategori::Bebas->value) {
                $subtotal = (float) ($input['nominal'] ?: 0);
            }

            $this->inputs[$id]['subtotal'] = $subtotal;
            $total += $subtotal;
        }

        $this->totalPemasukan = $total;
    }

    public function submit()
    {
        // Validasi dasar
        $this->validate([
            'tanggal' => 'required|date',
            'totalPemasukan' => 'required|numeric|min:0',
        ]);

        if ($this->totalPemasukan <= 0) {
            $this->addError('totalPemasukan', 'Total pemasukan tidak boleh nol. Silakan isi minimal satu transaksi.');

            return;
        }

        DB::beginTransaction();

        try {
            $transaksi = TransaksiHarian::create([
                'unit_wisata_id' => $this->unitId,
                'user_id' => Auth::id(),
                'tanggal' => $this->tanggal,
                'tanggal_akhir' => $this->tanggalAkhir,
                'total_pemasukan' => $this->totalPemasukan,
            ]);

            $date = Carbon::parse($this->tanggal);

            foreach ($this->inputs as $id => $input) {
                $subtotal = $input['subtotal'];
                if ($subtotal > 0) {
                    $kategori = $input['kategori'];
                    $hargaSatuan = 0;
                    $qty = null;

                    if ($input['tipe'] === TipeKategori::HargaXQty->value) {
                        $qty = (int) $input['qty'];
                        $hargaSatuan = $kategori->hargaSaat($date);
                    } elseif ($input['tipe'] === TipeKategori::Flat->value) {
                        $hargaSatuan = $kategori->hargaSaat($date);
                    }

                    TransaksiDetail::create([
                        'transaksi_harian_id' => $transaksi->id,
                        'kategori_transaksi_id' => $id,
                        'qty' => $qty,
                        'harga_satuan' => $hargaSatuan,
                        'subtotal' => $subtotal,
                    ]);
                }
            }

            DB::commit();

            // Reset form
            $this->initKategoriInputs();
            $this->totalPemasukan = 0;

            // Jika pakai Flux toast, bisa dispatch event atau panggil flash message
            session()->flash('status', 'Transaksi berhasil disimpan!');

            // Redirect ke halaman yang sama untuk merender ulang state yang bersih
            return $this->redirect(route('dashboard.unit'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'Terjadi kesalahan saat menyimpan transaksi: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kepala-unit.input-transaksi-harian');
    }
}
