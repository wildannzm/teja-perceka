<?php

namespace App\Livewire\KepalaUnit;

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Models\JurnalUmum;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\TransaksiDetail;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Computed;
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

    public bool $sudahInput = false;

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

        $this->checkSudahInput();
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

        $this->checkSudahInput();

        // Hitung ulang semua subtotal karena harga mungkin berbeda di tanggal yang baru
        $this->calculateAllSubtotals();
    }

    public function updatedInputs()
    {
        $this->calculateAllSubtotals();
    }

    private function checkSudahInput()
    {
        if ($this->isMingguan) {
            $this->sudahInput = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->where('tanggal', $this->tanggal)
                ->where('tanggal_akhir', $this->tanggalAkhir)
                ->exists();
        } else {
            $this->sudahInput = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->whereDate('tanggal', $this->tanggal)
                ->exists();
        }
    }

    #[Computed]
    public function kategoriList()
    {
        return KategoriTransaksi::where('unit_wisata_id', $this->unitId)
            ->where('jenis', JenisTransaksi::Pemasukan)
            ->get()->keyBy('id');
    }

    private function initKategoriInputs()
    {
        $this->inputs = [];
        $currentYear = Carbon::parse($this->tanggal)->year;

        foreach ($this->kategoriList as $kategori) {
            // Logic khusus untuk kategori Tahunan
            if ($kategori->tipe === TipeKategori::Tahunan) {
                // Cek apakah sudah pernah diinput di tahun berjalan
                $sudahAdaTahunan = TransaksiDetail::where('kategori_transaksi_id', $kategori->id)
                    ->whereHas('transaksiHarian', function ($query) use ($currentYear) {
                        $query->whereYear('tanggal', $currentYear);
                    })->exists();

                if ($sudahAdaTahunan) {
                    continue; // Sembunyikan jika sudah pernah diinput tahun ini
                }
            }

            $this->inputs[$kategori->id] = [
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
            $kategori = $this->kategoriList->get($id);
            if (!$kategori) continue;
            
            $subtotal = 0;

            if ($input['tipe'] === TipeKategori::HargaXQty->value || $input['tipe'] === TipeKategori::Tahunan->value) {
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
                    $kategori = $this->kategoriList->get($id);
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

            // --- OTOMATISASI JURNAL UMUM ---

            $akunKas = KodeAkun::where('kode', '1-1100')->first();
            if (! $akunKas) {
                throw new \Exception('Akun Kas (1-1100) tidak ditemukan di sistem. Harap hubungi administrator.');
            }

            // Prefix: 'D' (Pemasukan) + Kode Unit Wisata
            $kodeUnit = strtoupper($this->unit->kode ?? 'XX');
            $prefixNomor = 'D'.$kodeUnit;

            // Generate nomor bukti aman dari race condition (berdasarkan bulan dan tahun berjalan)
            $lastJurnal = JurnalUmum::where('nomor_bukti', 'like', $prefixNomor.'%')
                ->whereMonth('tanggal', $date->month)
                ->whereYear('tanggal', $date->year)
                ->lockForUpdate()
                ->orderBy('nomor_bukti', 'desc')
                ->first();

            $nextUrut = 1;
            if ($lastJurnal) {
                $lastUrut = (int) substr($lastJurnal->nomor_bukti, -3);
                $nextUrut = $lastUrut + 1;
            }

            $nomorBukti = $prefixNomor.str_pad($nextUrut, 3, '0', STR_PAD_LEFT);
            $keteranganJurnal = 'Pemasukan Harian - '.$this->unit->nama;

            // 1. Catat Debet ke Kas
            JurnalUmum::create([
                'nomor_bukti' => $nomorBukti,
                'tanggal' => $this->tanggal,
                'keterangan' => $keteranganJurnal,
                'kode_akun_id' => $akunKas->id,
                'debet' => $this->totalPemasukan,
                'kredit' => 0,
                'transaksi_harian_id' => $transaksi->id,
                'unit_wisata_id' => $this->unitId,
            ]);

            // 2. Kelompokkan Kredit per kode_akun_id dari input yang ada
            $kreditGroup = [];
            foreach ($this->inputs as $id => $input) {
                $subtotal = $input['subtotal'];
                if ($subtotal > 0) {
                    $kategori = $this->kategoriList->get($id);
                    $akunId = $kategori->kode_akun_id;
                    if (! $akunId) {
                        throw new \Exception('Kategori "'.$kategori->nama.'" belum terhubung ke Kode Akun (Chart of Account).');
                    }

                    if (! isset($kreditGroup[$akunId])) {
                        $kreditGroup[$akunId] = 0;
                    }
                    $kreditGroup[$akunId] += $subtotal;
                }
            }

            // 3. Catat Kredit untuk masing-masing akun pendapatan
            foreach ($kreditGroup as $akunId => $jumlahKredit) {
                JurnalUmum::create([
                    'nomor_bukti' => $nomorBukti,
                    'tanggal' => $this->tanggal,
                    'keterangan' => $keteranganJurnal,
                    'kode_akun_id' => $akunId,
                    'debet' => 0,
                    'kredit' => $jumlahKredit,
                    'transaksi_harian_id' => $transaksi->id,
                    'unit_wisata_id' => $this->unitId,
                ]);
            }

            // --- AKHIR JURNAL UMUM ---

            DB::commit();

            // Reset form
            $this->initKategoriInputs();
            $this->totalPemasukan = 0;

            // Jika pakai Flux toast, bisa dispatch event atau panggil flash message
            session()->flash('status', 'Transaksi berhasil disimpan!');

            // Redirect ke halaman yang sama untuk merender ulang state yang bersih
            return $this->redirect(route('unit.input-transaksi'), navigate: true);

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
