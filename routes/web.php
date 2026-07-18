<?php

use App\Livewire\Bendahara\Dashboard as BendaharaDashboard;
use App\Livewire\Bendahara\Report as BendaharaReport;
use App\Livewire\Bendahara\TransactionList as BendaharaTransactionList;
use App\Livewire\KepalaDesa\Dashboard as KepalaDesaDashboard;
use App\Livewire\KepalaDesa\Report as KepalaDesaReport;
use App\Livewire\KepalaDesa\UserManager as KepalaDesaUserManager;
use App\Livewire\KepalaUnit\RiwayatTransaksi;
use App\Livewire\Sekretaris\Dashboard as SekretarisDashboard;
use App\Livewire\Sekretaris\Report as SekretarisReport;
use App\Livewire\Sekretaris\TransactionList as SekretarisTransactionList;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Redirect otomatis sesuai role setelah login
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('kepala_unit')) {
            return redirect()->route('dashboard.unit');
        }
        if ($user->hasAnyRole(['sekretaris', 'bendahara'])) {
            return redirect()->route('dashboard.keuangan');
        }
        if ($user->hasRole('direktur_bumdes')) {
            return redirect()->route('dashboard.bumdes');
        }
        if ($user->hasRole('kepala_desa')) {
            return redirect()->route('kepala-desa.dashboard');
        }
        if ($user->hasRole('pengawas')) {
            return redirect()->route('dashboard.laporan');
        }

        abort(403, 'Role tidak dikenali.');
    })->name('dashboard');

    // ===== Kepala Unit =====
    Route::middleware(['role:kepala_unit'])->group(function () {
        Route::get('dashboard/unit', function () {
            return view('dashboard.unit');
        })->name('dashboard.unit');

        Route::get('unit/riwayat-transaksi', RiwayatTransaksi::class)
            ->name('unit.riwayat-transaksi');
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
    Route::middleware(['role:direktur_bumdes'])->get('dashboard/bumdes', function () {
        return view('dashboard.bumdes');
    })->name('dashboard.bumdes');

    // ===== Pengawas =====
    Route::middleware(['role:pengawas'])->get('dashboard/laporan', function () {
        return view('dashboard.laporan');
    })->name('dashboard.laporan');

});

require __DIR__.'/settings.php';
