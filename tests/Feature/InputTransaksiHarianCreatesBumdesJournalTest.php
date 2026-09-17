<?php

use App\Enums\JenisTransaksi;
use App\Enums\TipeKategori;
use App\Livewire\KepalaUnit\InputTransaksiHarian;
use App\Livewire\LaporanLabaRugi\AlokasiLaba;
use App\Livewire\Transaksi\TabJurnal;
use App\Models\JurnalUmum;
use App\Models\KategoriHargaRiwayat;
use App\Models\KategoriTransaksi;
use App\Models\KodeAkun;
use App\Models\TransaksiHarian;
use App\Models\UnitWisata;
use App\Models\User;
use App\Support\SaldoKasBumdes;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

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

    $this->serviceRevenueAccount = KodeAkun::firstOrCreate(
        ['kode' => '4-2000'],
        ['nama' => 'Pendapatan Jasa', 'tipe' => 'pendapatan']
    );

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
});

test('submitting income creates unit journal and shows calculated DBM in BUMDes tab jurnal', function () {
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', now()->format('Y-m-d'))
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    // Unit-level journal (DWA prefix, with unit_wisata_id)
    $unitJournals = JurnalUmum::where('nomor_bukti', 'like', 'DWA%')->get();
    expect($unitJournals)->toHaveCount(2); // 1 debit cash + 1 credit revenue
    expect($unitJournals->first()->unit_wisata_id)->toBe($this->unit->id);

    // No duplicate daily DBM rows saved in database
    $dbmRows = JurnalUmum::where('nomor_bukti', 'like', 'DBM%')->get();
    expect($dbmRows)->toBeEmpty();

    // In BUMDes TabJurnal, calculated dynamically as single DBM entry for period
    Role::firstOrCreate(['name' => 'direktur_bumdes']);
    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $tabJurnal = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);

    $groups = $tabJurnal->transactions['groups'];
    expect($groups->has('DBM001'))->toBeTrue();

    $dbmGroup = $groups->get('DBM001');
    expect($dbmGroup->first()->keterangan)->toBe('Pendapatan Unit Wisata Alam');
    expect($dbmGroup->where('debet', '>', 0)->first()->debet)->toBe(50000.0);
    expect($dbmGroup->where('kredit', '>', 0)->first()->kredit)->toBe(50000.0);
});

test('editing income updates unit journal and recalculates BUMDes DBM entry', function () {
    // Create initial transaction
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', now()->format('Y-m-d'))
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    $transactionId = TransaksiHarian::first()->id;

    // Edit: change qty to 10
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class, ['editId' => $transactionId])
        ->set('inputs.'.$this->category->id.'.qty', '10')
        ->call('submit');

    $unitJournals = JurnalUmum::where('nomor_bukti', 'like', 'DWA%')->get();
    expect($unitJournals)->toHaveCount(2);

    $unitDebit = $unitJournals->where('debet', '>', 0)->first();
    expect($unitDebit->debet)->toBe(100000.0); // 10 x 10,000

    Role::firstOrCreate(['name' => 'direktur_bumdes']);
    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $tabJurnal = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);

    $groups = $tabJurnal->transactions['groups'];
    $dbmGroup = $groups->get('DBM001');
    expect($dbmGroup->where('debet', '>', 0)->first()->debet)->toBe(100000.0);
});

test('direktur report shows only DBM and KBM journals', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    // Create income from unit
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', now()->format('Y-m-d'))
        ->set('inputs.'.$this->category->id.'.qty', '3')
        ->call('submit');

    // Create a KBM expense journal directly
    JurnalUmum::create([
        'nomor_bukti' => 'KBM001',
        'tanggal' => now()->format('Y-m-d'),
        'keterangan' => 'Biaya Test',
        'kode_akun_id' => $this->cashAccount->id,
        'debet' => 0,
        'kredit' => 5000,
        'unit_wisata_id' => null,
    ]);

    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $component = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);

    $groups = $component->transactions['groups'];

    // Should contain DBM and KBM entries, but NOT DWA
    $allKeys = $groups->keys()->toArray();
    foreach ($allKeys as $key) {
        expect($key)->toMatch('/^(DBM|KBM)/');
    }
    expect($allKeys)->not->toBeEmpty();
});

test('tab jurnal shows saldo kas bulan sebelumnya when prior cash balance exists', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    // Create prior month transaction directly via TransaksiHarian
    $priorDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => $priorDate,
        'total_pemasukan' => 1000000,
        'total_pengeluaran' => 0,
    ]);

    // Current month transaction
    $currentDate = now()->startOfMonth()->addDays(2)->format('Y-m-d');
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', $currentDate)
        ->set('inputs.'.$this->category->id.'.qty', '2')
        ->call('submit');

    // Prior month creates DBM001 (Saldo Kas), current month income becomes DBM002
    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $tabJurnal = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);

    $groups = $tabJurnal->transactions['groups'];
    expect($groups->has('DBM001'))->toBeTrue();
    expect($groups->has('DBM002'))->toBeTrue();

    $openingBalanceGroup = $groups->get('DBM001');
    $priorMonthName = now()->startOfMonth()->subMonth()->translatedFormat('F');
    expect($openingBalanceGroup->first()->keterangan)->toBe('Saldo Kas '.$priorMonthName);
    expect($openingBalanceGroup->where('debet', '>', 0)->first()->debet)->toBe(1000000.0);
    expect($openingBalanceGroup->where('kredit', '>', 0)->first()->kredit)->toBe(1000000.0);

    $unitGroup = $groups->get('DBM002');
    expect($unitGroup->first()->keterangan)->toBe('Pendapatan Unit Wisata Alam');
    expect($unitGroup->where('debet', '>', 0)->first()->debet)->toBe(20000.0);

    expect($tabJurnal->totalDebet)->toBe(1020000.0); // 1,000,000 opening + 20,000 current
    expect($tabJurnal->totalKredit)->toBe(1020000.0);
});

test('direktur can filter tab jurnal by all units or specific unit', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', now()->format('Y-m-d'))
        ->set('inputs.'.$this->category->id.'.qty', '1')
        ->call('submit');

    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    // Test specific unit
    $unitTab = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'unitId' => $this->unit->id,
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);
    $unitGroups = $unitTab->transactions['groups'];
    expect($unitGroups->keys()->first())->toStartWith('DWA');

    // Test 'semua' units
    $allUnitsTab = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'unitId' => 'semua',
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);
    $allUnitsGroups = $allUnitsTab->transactions['groups'];
    expect($allUnitsGroups->keys()->first())->toStartWith('DWA');

    // Test 'bumdes'
    $bumdesTab = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'unitId' => 'bumdes',
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);
    $bumdesGroups = $bumdesTab->transactions['groups'];
    expect($bumdesGroups->keys()->first())->toStartWith('DBM');
});

test('alokasi laba calculates profit from bumdes journals only', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    // Create unit income: 5 x 10,000 = 50,000
    Livewire::actingAs($this->user)
        ->test(InputTransaksiHarian::class)
        ->set('tanggal', now()->format('Y-m-d'))
        ->set('inputs.'.$this->category->id.'.qty', '5')
        ->call('submit');

    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $component = Livewire::actingAs($director)
        ->test(AlokasiLaba::class, [
            'mode' => 'bulanan',
            'periode' => now()->format('Y-m'),
        ]);

    expect($component->labaBersih)->toBe(50000.0);
});

test('daily filter does not create saldo kas bulan lalu even if prior transactions exist', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    // Yesterday transaction
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => now()->subDay()->format('Y-m-d'),
        'total_pemasukan' => 100000,
        'total_pengeluaran' => 0,
    ]);

    // Today transaction
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => now()->format('Y-m-d'),
        'total_pemasukan' => 50000,
        'total_pengeluaran' => 0,
    ]);

    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    // In daily mode for today: only today's unit revenue, no "Saldo Kas"
    $tabJurnal = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'harian',
            'tanggal' => now()->format('Y-m-d'),
        ]);

    $groups = $tabJurnal->transactions['groups'];
    expect($groups->has('DBM001'))->toBeTrue();

    // DBM001 must be today's revenue, NOT "Saldo Kas"
    $firstGroup = $groups->get('DBM001');
    expect($firstGroup->first()->keterangan)->toBe('Pendapatan Unit Wisata Alam');
    expect($firstGroup->where('debet', '>', 0)->first()->debet)->toBe(50000.0);
    expect($tabJurnal->totalDebet)->toBe(50000.0);
});

test('bumdes journal unit revenue calculates net income (pemasukan minus pengeluaran)', function () {
    Role::firstOrCreate(['name' => 'direktur_bumdes']);

    // Unit transaction with 50,000 income and 15,000 expense
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => now()->format('Y-m-d'),
        'total_pemasukan' => 50000,
        'total_pengeluaran' => 15000,
    ]);

    $director = User::factory()->create();
    $director->assignRole('direktur_bumdes');

    $tabJurnal = Livewire::actingAs($director)
        ->test(TabJurnal::class, [
            'mode' => 'bulanan',
            'bulan' => now()->format('Y-m'),
        ]);

    $groups = $tabJurnal->transactions['groups'];
    $firstGroup = $groups->get('DBM001');
    expect($firstGroup->first()->keterangan)->toBe('Pendapatan Unit Wisata Alam');
    // Net: 50,000 - 15,000 = 35,000
    expect($firstGroup->where('debet', '>', 0)->first()->debet)->toBe(35000.0);
    expect($firstGroup->where('kredit', '>', 0)->first()->kredit)->toBe(35000.0);
    expect($tabJurnal->totalDebet)->toBe(35000.0);
});

test('SaldoKasBumdes English methods compute correct opening balance and net unit revenue', function () {
    $startDate = now()->startOfMonth()->format('Y-m-d');
    $endDate = now()->endOfMonth()->format('Y-m-d');

    // Add prior transaction
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => now()->subMonth()->startOfMonth()->format('Y-m-d'),
        'total_pemasukan' => 300000,
        'total_pengeluaran' => 50000,
    ]);

    // Current month transaction
    TransaksiHarian::create([
        'user_id' => $this->user->id,
        'unit_wisata_id' => $this->unit->id,
        'tanggal' => now()->startOfMonth()->addDay()->format('Y-m-d'),
        'total_pemasukan' => 200000,
        'total_pengeluaran' => 50000,
    ]);

    // Opening balance should be 300,000 - 50,000 = 250,000
    expect(SaldoKasBumdes::getOpeningBalance($startDate))->toBe(250000.0);

    // Current net income per unit should be 200,000 - 50,000 = 150,000
    $netPerUnit = SaldoKasBumdes::getNetIncomePerUnit($startDate, $endDate);
    expect($netPerUnit)->toHaveCount(1);
    expect($netPerUnit->first()['amount'])->toBe(150000.0);

    // Total net unit income
    expect(SaldoKasBumdes::getTotalNetUnitIncome($startDate, $endDate))->toBe(150000.0);

    // Virtual entries
    $virtualEntries = SaldoKasBumdes::getBumdesVirtualEntries($startDate, $endDate, 'bulanan');
    expect($virtualEntries)->not->toBeEmpty();
});
