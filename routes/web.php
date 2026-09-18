<?php

use App\Http\Controllers\ImpersonationController;
use App\Livewire\Assets\ManageAssets;
use App\Livewire\BumdesDirector\Dashboard as DirekturBumdesDashboard;
use App\Livewire\BumdesDirector\ManageUnitAccounts;
use App\Livewire\Expenses\RecordExpense;
use App\Livewire\ProfitLoss\ProfitAllocation;
use App\Livewire\ProfitLoss\ProfitLoss;
use App\Livewire\Reports\GeneralLedger;
use App\Livewire\Reports\TrialBalance;
use App\Livewire\Secretary\Dashboard as SekretarisDashboard;
use App\Livewire\SuperAdmin\ActivityLogs as SuperAdminActivityLogs;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\SuperAdmin\UserManager as SuperAdminUserManager;
use App\Livewire\Supervisor\Dashboard as PengawasDashboard;
use App\Livewire\Transactions\HistoryRecap;
use App\Livewire\Treasurer\Dashboard as BendaharaDashboard;
use App\Livewire\UnitHead\Dashboard as KepalaUnitDashboard;
use App\Livewire\UnitHead\ManageRevenue;
use App\Livewire\UnitHead\RecordExpense as UnitHeadRecordExpense;
use App\Livewire\VillageHead\Dashboard as KepalaDesaDashboard;
use App\Livewire\VillageHead\UserManager as KepalaDesaUserManager;
use App\Models\DailyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Root redirect ke /login
Route::redirect('/', '/login')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {

    // Auto-redirect by role after login
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();

        if ($user->hasRole('super_admin')) {
            return redirect()->route('super-admin.dashboard');
        }
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

    // ===== Unit heads =====
    Route::middleware(['role:kepala_unit'])->group(function () {
        Route::get('unit/dashboard', KepalaUnitDashboard::class)
            ->name('dashboard.unit');

        Route::get('unit/input-transaksi', function () {
            return view('dashboard.unit-input');
        })->name('unit.input-transaksi');

        Route::get('unit/riwayat-transaksi', fn () => redirect()->route('riwayat-rekap'))
            ->name('unit.riwayat-transaksi');

        Route::get('unit/kelola-harga', ManageRevenue::class)
            ->name('unit.kelola-harga');

        Route::get('unit/catat-pengeluaran', UnitHeadRecordExpense::class)
            ->name('unit.catat-pengeluaran');

        Route::get('unit/edit-transaksi/{dailyTransaction}', function (DailyTransaction $dailyTransaction) {
            return view('dashboard.unit-edit', ['editId' => $dailyTransaction->id]);
        })->name('unit.edit-transaksi');
    });

    // ===== Secretary =====
    Route::middleware(['role:sekretaris'])->prefix('sekretaris')->group(function () {
        Route::get('/dashboard', SekretarisDashboard::class)->name('sekretaris.dashboard');
    });

    // ===== Treasurer =====
    Route::middleware(['role:bendahara'])->prefix('bendahara')->group(function () {
        Route::get('/dashboard', BendaharaDashboard::class)->name('bendahara.dashboard');
    });

    // Joint Secretary & Treasurer dashboard (shared placeholder page)
    Route::middleware(['role:sekretaris|bendahara'])->get('dashboard/keuangan', function () {
        return view('dashboard.finance');
    })->name('dashboard.keuangan');

    // ===== Village head =====
    Route::middleware(['role:kepala_desa'])->prefix('kepala-desa')->group(function () {
        Route::get('/dashboard', KepalaDesaDashboard::class)->name('kepala-desa.dashboard');
        Route::get('/users', KepalaDesaUserManager::class)->name('kepala-desa.users');
    });

    // ===== BUMDes director =====
    Route::middleware(['role:direktur_bumdes'])->prefix('direktur-bumdes')->group(function () {
        Route::get('/dashboard', DirekturBumdesDashboard::class)->name('direktur-bumdes.dashboard');

        Route::get('/kelola-akun', ManageUnitAccounts::class)->name('direktur-bumdes.kelola-akun');
    });

    // ===== Supervisor =====
    Route::middleware(['role:pengawas'])->prefix('pengawas')->group(function () {
        Route::get('/dashboard', PengawasDashboard::class)->name('pengawas.dashboard');
    });

    // ===== Expenses (shared: direktur_bumdes, sekretaris, bendahara) =====
    Route::middleware(['role:direktur_bumdes|sekretaris|bendahara'])
        ->get('pengeluaran/catat', RecordExpense::class)
        ->name('pengeluaran.catat');

    // ===== Centralized financial reports =====
    Route::middleware(['role:kepala_unit|sekretaris|bendahara|direktur_bumdes|kepala_desa|pengawas'])->group(function () {
        Route::get('laporan/laba-rugi', ProfitLoss::class)->name('laporan.laba-rugi');
        Route::get('laporan/buku-besar', GeneralLedger::class)->name('laporan.buku-besar');
        Route::get('laporan/neraca-saldo', TrialBalance::class)->name('laporan.neraca-saldo');
    });

    // ===== Profit allocation (excluding unit heads) =====
    Route::middleware(['role:sekretaris|bendahara|direktur_bumdes|kepala_desa|pengawas'])->group(function () {
        Route::get('laporan/alokasi-laba', ProfitAllocation::class)->name('laporan.alokasi-laba');
    });

    // ===== Assets (Direktur, Secretary, Treasurer can CRUD; others read-only via policy) =====
    Route::middleware(['role:direktur_bumdes|sekretaris|bendahara|kepala_desa|pengawas|kepala_unit'])
        ->get('asset', ManageAssets::class)
        ->name('asset.kelola');

    // ===== Income (all roles) — redirect to History & Recap =====
    Route::middleware(['role:kepala_unit|sekretaris|bendahara|direktur_bumdes|kepala_desa|pengawas'])
        ->get('pendapatan', fn () => redirect()->route('riwayat-rekap', ['tab' => 'revenue']))
        ->name('pendapatan');

    // ===== History & Recap (combined Income + General Journal) =====
    Route::middleware(['role:kepala_unit|sekretaris|bendahara|direktur_bumdes|kepala_desa|pengawas'])
        ->get('riwayat-rekap', HistoryRecap::class)
        ->name('riwayat-rekap');

    // Legacy redirect: unit/riwayat-transaksi → riwayat-rekap
    Route::middleware(['role:kepala_unit'])
        ->get('unit/riwayat-transaksi-lama', fn () => redirect()->route('riwayat-rekap'))
        ->name('unit.riwayat-transaksi.redirect');

    // ===== Super Admin =====
    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->name('super-admin.dashboard');
        Route::get('/users', SuperAdminUserManager::class)->name('super-admin.users');
        Route::get('/activity-logs', SuperAdminActivityLogs::class)->name('super-admin.activity-logs');
    });

    // ===== Impersonation routes =====
    Route::post('/impersonate/start/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

});

require __DIR__.'/settings.php';
