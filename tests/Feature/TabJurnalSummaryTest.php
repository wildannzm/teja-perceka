<?php

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Livewire\KepalaUnit\InputTransaksiHarian;
use App\Livewire\Transaksi\RiwayatRekap;
use App\Livewire\Transaksi\TabJurnal;
use App\Models\JurnalUmum;
use App\Models\KategoriHargaRiwayat;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\UnitWisata;
use App\Models\User;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'kepala_unit']);

    $this->unit = UnitWisata::create([
        'nama' => 'Unit Wisata Alam',
        'kode' => 'WA',
        'frekuensi_input' => 'harian',
    ]);

    $this->user = User::factory()->create(['unit_wisata_id' => $this->unit->id]);
    $this->user->assignRole('kepala_unit');

    $this->cashAccount = KodeAkun::create([
        'kode' => '1-1100',
        'nama' => 'Kas',
        'tipe' => 'aktiva',
    ]);

    $this->revenueAccount = KodeAkun::create([
        'kode' => '4-1100',
        'nama' => 'Pendapatan Tiket',
        'tipe' => 'pendapatan',
    ]);

    $this->expenseAccount = KodeAkun::create([
        'kode' => '6-0001',
        'nama' => 'Biaya Operasional',
        'tipe' => 'beban',
    ]);

    $this->category = KategoriTransaksi::create([
        'unit_wisata_id' => $this->unit->id,
        'kode_akun_id' => $this->revenueAccount->id,
        'nama' => 'Tiket Masuk',
        'tipe' => TipeKategori::HargaXQty,
        'jenis' => JenisTransaksi::Pemasukan,
    ]);

    KategoriHargaRiwayat::create([
        'kategori_transaksi_id' => $this->category->id,
        'harga' => 10000,
        'berlaku_dari' => now()->subYear(),
    ]);

    $this->month = now()->format('Y-m');
    $this->dateA = now()->startOfMonth()->addDays(0)->format('Y-m-d');
    $this->dateB = now()->startOfMonth()->addDays(1)->format('Y-m-d');
});

test('summary accumulates same description into one rowspan group with first voucher shown', function () {
    // Two daily inputs, same unit, same auto-description, same month.
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $this->dateA)
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $this->dateB)
        ->set('inputs.'.$this->category->id.'.qty', '2')
        ->call('submit');

    // Detailed still stores 4 rows (2 vouchers x 2 lines).
    expect(JurnalUmum::count())->toBe(4);

    $tab = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'bulanan',
            'bulan' => $this->month,
        ]);

    $summary = $tab->get('summaryRows');
    $groups = $summary['groups'];

    // Same description across 2 vouchers: 1 display group (rowspan merge),
    // 2 account rows, totals accumulated, first voucher shown.
    expect($groups)->toHaveCount(1);

    $rows = $groups->first();
    expect($rows)->toHaveCount(2);

    // Debit row first, then credit row.
    expect($rows->pluck('kode')->all())->toBe(['1-1100', '4-1100']);

    $debit = $rows->firstWhere('kode', '1-1100');
    $credit = $rows->firstWhere('kode', '4-1100');

    expect((float) $debit->totalDebit)->toBe(70000.0);
    expect((float) $credit->totalCredit)->toBe(70000.0);
    expect((int) $debit->voucherCount)->toBe(2);
    expect($debit->firstVoucher)->toEndWith('001');

    // Displayed date is the period end for all rows.
    $monthEnd = now()->endOfMonth()->format('Y-m-d');
    expect($summary['displayDate'])->toBe($monthEnd);
});

test('summary keeps different descriptions on separate rows', function () {
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $this->dateA)
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    JurnalUmum::create([
        'nomor_bukti' => 'KWA001',
        'tanggal' => $this->dateA,
        'keterangan' => 'Test Expense',
        'kode_akun_id' => $this->expenseAccount->id,
        'debet' => 15000,
        'kredit' => 0,
        'unit_wisata_id' => $this->unit->id,
    ]);
    JurnalUmum::create([
        'nomor_bukti' => 'KWA001',
        'tanggal' => $this->dateA,
        'keterangan' => 'Test Expense',
        'kode_akun_id' => $this->cashAccount->id,
        'debet' => 0,
        'kredit' => 15000,
        'unit_wisata_id' => $this->unit->id,
    ]);

    $tab = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'bulanan',
            'bulan' => $this->month,
        ]);

    $groups = $tab->get('summaryRows')['groups'];

    // Income pair + expense pair = 2 groups, no cross-description merge,
    // ordered by voucher number.
    expect($groups)->toHaveCount(2);
    expect($groups->flatten()->where('keterangan', 'Test Expense'))->toHaveCount(2);
    expect($groups[0]->first()->firstVoucher)->toBeLessThan($groups[1]->first()->firstVoucher);
});

test('view mode toggle lives on parent filter and reaches tab jurnal', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);
    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $page = Livewire::actingAs($director)
        ->test(RiwayatRekap::class, ['tab' => 'jurnal'])
        ->set('viewMode', 'detailed');

    expect($page->get('viewMode'))->toBe('detailed');
});

test('export pdf follows view mode filter', function () {
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $this->dateA)
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    $summary = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'bulanan',
            'bulan' => $this->month,
            'viewMode' => 'summary',
        ]);
    $summaryResponse = $summary->instance()->exportPdf();
    expect($summaryResponse)->toBeInstanceOf(StreamedResponse::class);
    expect($summaryResponse->headers->get('Content-Disposition'))->toContain('Summary');

    $detailed = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'bulanan',
            'bulan' => $this->month,
            'viewMode' => 'detailed',
        ]);
    $detailedResponse = $detailed->instance()->exportPdf();
    expect($detailedResponse)->toBeInstanceOf(StreamedResponse::class);
    expect($detailedResponse->headers->get('Content-Disposition'))->toContain('Detailed');
});

test('export pdf blocked in harian mode', function () {
    $tab = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'harian',
            'tanggal' => $this->dateA,
        ]);

    expect($tab->instance()->exportPdf())->toBeNull();
});

test('detailed yearly keeps same voucher number from different months in separate groups', function () {
    $dateA = now()->copy()->startOfYear()->addMonths(2)->format('Y-m-d');
    $dateB = now()->copy()->startOfYear()->addMonths(4)->format('Y-m-d');

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $dateA)
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $dateB)
        ->set('inputs.'.$this->category->id.'.qty', '3')
        ->call('submit');

    // Both months restart numbering: same DWA001 twice, must not merge.
    expect(JurnalUmum::where('nomor_bukti', 'DWA001')->count())->toBe(4);

    $tab = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'tahunan',
            'tahun' => now()->format('Y'),
            'viewMode' => 'detailed',
        ]);

    $groups = $tab->transactions['groups'];
    expect($groups)->toHaveCount(2);
    expect($groups->keys()->all())->each->toStartWith('DWA001|');
    foreach ($groups as $group) {
        expect($group)->toHaveCount(2);
    }
});

test('summary yearly accumulates same description across months with true voucher count', function () {
    $dateA = now()->copy()->startOfYear()->addMonths(2)->format('Y-m-d');
    $dateB = now()->copy()->startOfYear()->addMonths(4)->format('Y-m-d');

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $dateA)
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $dateB)
        ->set('inputs.'.$this->category->id.'.qty', '3')
        ->call('submit');

    $tab = Livewire::actingAs($this->user)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'tahunan',
            'tahun' => now()->format('Y'),
        ]);

    $summary = $tab->get('summaryRows');
    expect($summary['displayDate'])->toBe(now()->copy()->endOfYear()->format('Y-m-d'));

    $groups = $summary['groups'];
    expect($groups)->toHaveCount(1);

    $rows = $groups->first();
    expect((float) $rows->firstWhere('kode', '1-1100')->totalDebit)->toBe(80000.0);
    expect((int) $rows->firstWhere('kode', '1-1100')->voucherCount)->toBe(2);
});
