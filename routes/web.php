<?php

use App\Livewire\KepalaUnit\RiwayatTransaksi;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    // Rute untuk Sekretaris
    Route::middleware(['auth', 'role:Sekretaris'])->prefix('sekretaris')->group(function () {
        Route::get('/dashboard', \App\Livewire\Sekretaris\Dashboard::class)->name('sekretaris.dashboard');
        Route::get('/verifikasi', \App\Livewire\Sekretaris\TransactionVerification::class)->name('sekretaris.verifikasi');
        Route::get('/transaksi', \App\Livewire\Sekretaris\TransactionList::class)->name('sekretaris.transaksi');
        Route::get('/laporan', \App\Livewire\Sekretaris\Report::class)->name('sekretaris.laporan');
    });

    // Rute untuk Bendahara
    Route::middleware(['auth', 'role:Bendahara'])->prefix('bendahara')->group(function () {
        Route::get('/dashboard', \App\Livewire\Bendahara\Dashboard::class)->name('bendahara.dashboard');
        Route::get('/verifikasi', \App\Livewire\Bendahara\TransactionVerification::class)->name('bendahara.verifikasi');
        Route::get('/transaksi', \App\Livewire\Bendahara\TransactionList::class)->name('bendahara.transaksi');
        Route::get('/laporan', \App\Livewire\Bendahara\Report::class)->name('bendahara.laporan');
    });

    // Custom Dashboard Redirect
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
        if ($user->hasAnyRole(['kepala_desa', 'pengawas'])) {
            return redirect()->route('dashboard.laporan');
        }
        
        if ($user->hasRole('Bendahara')) {
            return redirect()->route('bendahara.dashboard');
        }

        abort(403, 'Role tidak dikenali.');
    })->name('dashboard');

    Route::middleware(['role:kepala_unit'])->get('dashboard/unit', function () {
        return view('dashboard.unit');
    })->name('dashboard.unit');

    Route::middleware(['role:kepala_unit'])
        ->get('unit/riwayat-transaksi', RiwayatTransaksi::class)
        ->name('unit.riwayat-transaksi');

    Route::middleware(['role:sekretaris|bendahara'])->get('dashboard/keuangan', function () {
        return view('dashboard.keuangan');
    })->name('dashboard.keuangan');

    Route::middleware(['role:direktur_bumdes'])->get('dashboard/bumdes', function () {
        return view('dashboard.bumdes');
    })->name('dashboard.bumdes');

    Route::middleware(['role:kepala_desa|pengawas'])->get('dashboard/laporan', function () {
        return view('dashboard.laporan');
    })->name('dashboard.laporan');
});

require __DIR__.'/settings.php';
