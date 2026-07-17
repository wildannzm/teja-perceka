<?php

use App\Livewire\KepalaUnit\RiwayatTransaksi;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        /** @var User $user */
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
