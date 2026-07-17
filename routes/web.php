<?php

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
        
        if ($user->hasRole('Kepala Desa')) {
            return redirect()->route('kepala-desa.dashboard');
        }
        
        if ($user->hasRole('Sekretaris')) {
            return redirect()->route('sekretaris.dashboard');
        }
        
        if ($user->hasRole('Bendahara')) {
            return redirect()->route('bendahara.dashboard');
        }

        return view('dashboard');
    })->middleware(['auth', 'verified'])->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:Kepala Desa'])->prefix('kepala-desa')->name('kepala-desa.')->group(function () {
    Route::get('/dashboard', \App\Livewire\KepalaDesa\Dashboard::class)->name('dashboard');
    Route::get('/report', \App\Livewire\KepalaDesa\Report::class)->name('report');
    Route::get('/users', \App\Livewire\KepalaDesa\UserManager::class)->name('users');
});

require __DIR__.'/settings.php';
