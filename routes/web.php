<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->hasRole('Kepala Desa')) {
            return redirect()->route('kepala-desa.dashboard');
        }
        return view('dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'verified', 'role:Kepala Desa'])->prefix('kepala-desa')->name('kepala-desa.')->group(function () {
    Route::get('/dashboard', \App\Livewire\KepalaDesa\Dashboard::class)->name('dashboard');
    Route::get('/report', \App\Livewire\KepalaDesa\Report::class)->name('report');
    Route::get('/users', \App\Livewire\KepalaDesa\UserManager::class)->name('users');
});

require __DIR__.'/settings.php';
