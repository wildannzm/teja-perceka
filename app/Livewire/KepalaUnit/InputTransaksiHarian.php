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
use Livewire\Attributes\Computed;
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

    /** When set, the component is in edit mode for this TransaksiHarian ID. */
    #[Locked]
    public ?int $editId = null;

    public bool $isEditing = false;

    // Holds the input state of each category
    // Format: [kategori_id => ['qty' => value, 'nominal' => value, 'aktif' => boolean, 'subtotal' => value]]
    public array $inputs = [];

    public bool $sudahInput = false;

    public bool $showDuplicateError = false;

    public float $totalPemasukan = 0;

    public function mount(?int $editId = null)
    {
        $user = Auth::user();

        // Restrict access to the kepala_unit role owning a unit_wisata
        if (! $user->hasRole('kepala_unit') || ! $user->unit_wisata_id) {
            abort(403, 'Akses ditolak. Anda bukan kepala unit yang valid.');
        }

        $this->unitId = $user->unit_wisata_id;
        $this->unit = UnitWisata::findOrFail($this->unitId);

        $today = Carbon::today();
        $this->isMingguan = $this->unit->frekuensi_input === 'mingguan';

        if ($editId) {
            $this->loadEditMode($editId);
        } else {
            if ($this->isMingguan) {
                // For weekly units (TPS), default to today as the specific entry date,
                // while tanggalAkhir marks the end of the current week period.
                $this->tanggal = $today->format('Y-m-d');
                $this->tanggalAkhir = $today->copy()->endOfWeek()->format('Y-m-d');
            } else {
                $this->tanggal = $today->format('Y-m-d');
            }

            $this->checkSudahInput();
            $this->initKategoriInputs();
        }
    }

    /**
     * Load an existing TransaksiHarian into edit mode.
     */
    private function loadEditMode(int $editId): void
    {
        $transaksi = TransaksiHarian::with('detail.kategoriTransaksi')
            ->where('unit_wisata_id', $this->unitId)
            ->findOrFail($editId);

        $this->editId = $editId;
        $this->isEditing = true;
        $this->tanggal = $transaksi->tanggal->format('Y-m-d');
        $this->tanggalAkhir = $transaksi->tanggal_akhir?->format('Y-m-d');

        // Build a lookup of existing detail values keyed by kategori_id
        $existingDetails = $transaksi->detail->keyBy('kategori_transaksi_id');

        $this->initKategoriInputs();

        // Overlay existing values onto the initialised inputs
        foreach ($existingDetails as $kategoriId => $detail) {
            if (! isset($this->inputs[$kategoriId])) {
                continue;
            }

            $tipe = $this->inputs[$kategoriId]['tipe'];

            if ($tipe === TipeKategori::HargaXQty->value || $tipe === TipeKategori::Tahunan->value) {
                $this->inputs[$kategoriId]['qty'] = $detail->qty ?? '';
            } elseif ($tipe === TipeKategori::Flat->value) {
                $this->inputs[$kategoriId]['aktif'] = $detail->subtotal > 0;
            } elseif ($tipe === TipeKategori::Bebas->value) {
                $this->inputs[$kategoriId]['nominal'] = $detail->subtotal > 0 ? (string) (int) $detail->subtotal : '';
            }
        }

        $this->calculateAllSubtotals();
    }

    public function updatedTanggal(): void
    {
        if ($this->isMingguan) {
            // For weekly units (TPS), the user picks a specific day within the week.
            // Keep their chosen date and derive tanggalAkhir as the end of that week.
            $date = Carbon::parse($this->tanggal);
            $this->tanggalAkhir = $date->copy()->endOfWeek()->format('Y-m-d');
        }

        if (! $this->isEditing) {
            $this->checkSudahInput();
        }

        // Recalculate all subtotals since prices may differ on the new date
        $this->calculateAllSubtotals();
    }

    public function updatedInputs()
    {
        $this->calculateAllSubtotals();
    }

    private function checkSudahInput(): void
    {
        $this->sudahInput = TransaksiHarian::where('unit_wisata_id', $this->unitId)
            ->whereDate('tanggal', $this->tanggal)
            ->exists();
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
            // Special rule for yearly categories: hide when already submitted this year,
            // except while editing (show every category from the original data).
            if (! $this->isEditing && $kategori->tipe === TipeKategori::Tahunan) {
                // Check whether it was already submitted in the current year
                $sudahAdaTahunan = TransaksiDetail::where('kategori_transaksi_id', $kategori->id)
                    ->whereHas('transaksiHarian', function ($query) use ($currentYear) {
                        $query->whereYear('tanggal', $currentYear);
                    })->exists();

                if ($sudahAdaTahunan) {
                    continue; // Hide when already submitted this year
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
            if (! $kategori) {
                continue;
            }

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
        // Base validation
        $this->validate([
            'tanggal' => 'required|date',
            'totalPemasukan' => 'required|numeric|min:0',
        ]);

        if ($this->totalPemasukan <= 0) {
            $this->addError('totalPemasukan', 'Total pemasukan tidak boleh nol. Silakan isi minimal satu transaksi.');

            return;
        }

        if (! $this->isEditing) {
            $this->checkSudahInput();
            if ($this->sudahInput) {
                $this->showDuplicateError = true;

                return;
            }
        }

        if ($this->isEditing && $this->editId) {
            return $this->executeUpdate();
        } else {
            return $this->executeCreate();
        }
    }

    private function executeCreate()
    {
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

            $this->createJurnalUmum($transaksi, $date);

            DB::commit();

            // Reset the form
            $this->initKategoriInputs();
            $this->totalPemasukan = 0;

            session()->flash('status', 'Transaksi berhasil disimpan!');

            // Redirect ke halaman yang sama untuk merender ulang state yang bersih
            return $this->redirect(route('unit.input-transaksi'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'Terjadi kesalahan saat menyimpan transaksi: '.$e->getMessage());
        }
    }

    private function executeUpdate()
    {
        DB::beginTransaction();

        try {
            $transaksi = TransaksiHarian::where('unit_wisata_id', $this->unitId)
                ->findOrFail($this->editId);

            $date = Carbon::parse($this->tanggal);

            // Update header
            $transaksi->update([
                'tanggal' => $this->tanggal,
                'tanggal_akhir' => $this->tanggalAkhir,
                'total_pemasukan' => $this->totalPemasukan,
                'user_id' => Auth::id(),
            ]);

            // Delete old details and regenerate
            $transaksi->detail()->delete();

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

            // Regenerate JurnalUmum: delete old entries and recreate
            JurnalUmum::where('transaksi_harian_id', $transaksi->id)->delete();
            $this->createJurnalUmum($transaksi, $date);

            DB::commit();

            session()->flash('status', 'Transaksi berhasil diperbarui!');
            session()->flash('swal', ['icon' => 'success', 'title' => 'Berhasil', 'text' => 'Transaksi berhasil diperbarui!']);

            return $this->redirect(route('riwayat-rekap'), navigate: true);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('submit', 'Terjadi kesalahan saat memperbarui transaksi: '.$e->getMessage());
        }
    }

    /**
     * Create JurnalUmum entries for a TransaksiHarian.
     *
     * @throws \Exception
     */
    private function createJurnalUmum(TransaksiHarian $transaksi, Carbon $date): void
    {
        $akunKas = KodeAkun::where('kode', '1-1100')->first();
        if (! $akunKas) {
            throw new \Exception('Akun Kas (1-1100) tidak ditemukan di sistem. Harap hubungi administrator.');
        }

        // Prefix: 'D' (Pemasukan) + Kode Unit Wisata
        $kodeUnit = strtoupper($this->unit->kode ?? 'XX');
        $prefixNomor = 'D'.$kodeUnit;

        $existingNumbers = JurnalUmum::where('nomor_bukti', 'like', $prefixNomor.'%')
            ->whereMonth('tanggal', $date->month)
            ->whereYear('tanggal', $date->year)
            ->lockForUpdate()
            ->pluck('nomor_bukti')
            ->map(fn($nomor) => (int) substr($nomor, -3))
            ->unique()
            ->toArray();

        $nextUrut = 1;
        while (in_array($nextUrut, $existingNumbers)) {
            $nextUrut++;
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
    }

    public function render()
    {
        return view('livewire.kepala-unit.input-transaksi-harian');
    }
}
