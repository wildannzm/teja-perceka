<?php

use App\Livewire\Bendahara\Dashboard as BendaharaDashboard;
use App\Livewire\Bendahara\Report as BendaharaReport;
use App\Livewire\Bendahara\TransactionList as BendaharaTransactionList;
use App\Livewire\DirekturBumdes\Dashboard as DirekturBumdesDashboard;
use App\Livewire\DirekturBumdes\KelolaAkunUnit;
use App\Livewire\DirekturBumdes\Report as DirekturBumdesReport;
use App\Livewire\DirekturBumdes\TransactionList as DirekturBumdesTransactionList;
use App\Livewire\KepalaDesa\Dashboard as KepalaDesaDashboard;
use App\Livewire\KepalaDesa\Report as KepalaDesaReport;
use App\Livewire\KepalaDesa\UserManager as KepalaDesaUserManager;
use App\Livewire\KepalaUnit\Dashboard as KepalaUnitDashboard;
use App\Livewire\KepalaUnit\KelolaHarga;
use App\Livewire\KepalaUnit\RiwayatTransaksi;
use App\Livewire\Pengawas\Dashboard as PengawasDashboard;
use App\Livewire\Pengawas\LihatJurnal as PengawasLihatJurnal;
use App\Livewire\Pengawas\Report as PengawasReport;
use App\Livewire\Pengeluaran\CatatPengeluaran;
use App\Livewire\Sekretaris\Dashboard as SekretarisDashboard;
use App\Livewire\Sekretaris\Report as SekretarisReport;
use App\Livewire\Sekretaris\TransactionList as SekretarisTransactionList;
use App\Livewire\LaporanLabaRugi\LabaRugi;
use App\Livewire\Laporan\BukuBesar;
use App\Livewire\Laporan\NeracaSaldo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root redirect ke /login
Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Redirect otomatis sesuai role setelah login
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('kepala_unit')) {
            return redirect()->route('dashboard.unit');
        }
        if ($user->hasRole('sekretaris')) {
            return redirect()->route('sekretaris.dashboard');
        }
        if ($user->hasRole('bendahara')) {
            return redirect()->route('bendahara.dashboard');
        }
        if ($user->hasRole('direktur_bumdes')) {
            return redirect()->route('direktur-bumdes.dashboard');
        }
        if ($user->hasRole('kepala_desa')) {
            return redirect()->route('kepala-desa.dashboard');
        }
        if ($user->hasRole('pengawas')) {
            return redirect()->route('pengawas.dashboard');
        }

        abort(403, 'Role tidak dikenali.');
    })->name('dashboard');

    // ===== Kepala Unit =====
    Route::middleware(['role:kepala_unit'])->group(function () {
        Route::get('unit/dashboard', KepalaUnitDashboard::class)
            ->name('dashboard.unit');

        Route::get('unit/input-transaksi', function () {
            return view('dashboard.unit-input');
        })->name('unit.input-transaksi');

        Route::get('unit/riwayat-transaksi', RiwayatTransaksi::class)
            ->name('unit.riwayat-transaksi');

        Route::get('unit/kelola-harga', KelolaHarga::class)
            ->name('unit.kelola-harga');
    });

    // ===== Sekretaris =====
    Route::middleware(['role:sekretaris'])->prefix('sekretaris')->group(function () {
        Route::get('/dashboard', SekretarisDashboard::class)->name('sekretaris.dashboard');
        Route::get('/transaksi', SekretarisTransactionList::class)->name('sekretaris.transaksi');
        Route::get('/laporan', SekretarisReport::class)->name('sekretaris.laporan');
    });

    // ===== Bendahara =====
    Route::middleware(['role:bendahara'])->prefix('bendahara')->group(function () {
        Route::get('/dashboard', BendaharaDashboard::class)->name('bendahara.dashboard');
        Route::get('/transaksi', BendaharaTransactionList::class)->name('bendahara.transaksi');
        Route::get('/laporan', BendaharaReport::class)->name('bendahara.laporan');
    });

    // Dashboard gabungan Sekretaris & Bendahara (halaman placeholder umum)
    Route::middleware(['role:sekretaris|bendahara'])->get('dashboard/keuangan', function () {
        return view('dashboard.keuangan');
    })->name('dashboard.keuangan');

    // ===== Kepala Desa =====
    Route::middleware(['role:kepala_desa'])->prefix('kepala-desa')->group(function () {
        Route::get('/dashboard', KepalaDesaDashboard::class)->name('kepala-desa.dashboard');
        Route::get('/report', KepalaDesaReport::class)->name('kepala-desa.report');
        Route::get('/users', KepalaDesaUserManager::class)->name('kepala-desa.users');
    });

    // ===== Direktur BUMDes =====
    Route::middleware(['role:direktur_bumdes'])->prefix('direktur-bumdes')->group(function () {
        Route::get('/dashboard', DirekturBumdesDashboard::class)->name('direktur-bumdes.dashboard');
        Route::get('/transaksi', DirekturBumdesTransactionList::class)->name('direktur-bumdes.transaksi');
        Route::get('/laporan', DirekturBumdesReport::class)->name('direktur-bumdes.laporan');
        Route::get('/kelola-akun', KelolaAkunUnit::class)->name('direktur-bumdes.kelola-akun');
    });

    // ===== Pengawas =====
    Route::middleware(['role:pengawas'])->prefix('pengawas')->group(function () {
        Route::get('/dashboard', PengawasDashboard::class)->name('pengawas.dashboard');
        Route::get('/transaksi', PengawasLihatJurnal::class)->name('pengawas.transaksi');
        Route::get('/laporan', PengawasReport::class)->name('pengawas.laporan');
    });

    // ===== Pengeluaran (Shared: direktur_bumdes, sekretaris, bendahara) =====
    Route::middleware(['role:direktur_bumdes|sekretaris|bendahara'])
        ->get('pengeluaran/catat', CatatPengeluaran::class)
        ->name('pengeluaran.catat');

    // ===== Laporan Keuangan Terpusat =====
    Route::middleware(['role:kepala_unit|sekretaris|bendahara|direktur_bumdes|kepala_desa|pengawas'])->group(function () {
        Route::get('laporan/laba-rugi', LabaRugi::class)->name('laporan.laba-rugi');
        Route::get('laporan/buku-besar', BukuBesar::class)->name('laporan.buku-besar');
        Route::get('laporan/neraca-saldo', NeracaSaldo::class)->name('laporan.neraca-saldo');
    });

});

require __DIR__.'/settings.php';
