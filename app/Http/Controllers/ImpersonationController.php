<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ImpersonationController extends Controller
{
    /**
     * Start impersonating the target user.
     */
    public function start(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('impersonate');

        $admin = $request->user();

        // Prevent self-impersonation or nested impersonation
        if ($admin->id === $user->id || $request->session()->has('impersonator_id')) {
            return back()->with('error', 'Permintaan penyamaran tidak valid.');
        }

        $adminId = $admin->id;

        // Record activity log
        ActivityLog::log(
            'impersonate_start',
            "Admin {$admin->name} ({$admin->email}) mulai mengimpersonasi pengguna {$user->name} ({$user->email})",
            [
                'admin_id' => $adminId,
                'target_user_id' => $user->id,
                'target_user_email' => $user->email,
            ],
            $adminId
        );

        // Login as target user (session ID is migrated/regenerated)
        Auth::login($user);

        // Store original admin ID in new target user session and save explicitly
        $request->session()->put('impersonator_id', $adminId);
        $request->session()->save();

        return redirect()->route('dashboard')->with('status', "Anda sekarang mengimpersonasi akun {$user->name}.");
    }

    /**
     * Stop impersonating and return to the original admin account.
     */
    public function stop(Request $request): RedirectResponse
    {
        if (! $request->session()->has('impersonator_id')) {
            return redirect()->route('dashboard');
        }

        $impersonatedUser = $request->user();
        $adminId = $request->session()->pull('impersonator_id');
        $admin = User::findOrFail($adminId);

        // Record activity log
        ActivityLog::log(
            'impersonate_stop',
            "Admin {$admin->name} ({$admin->email}) menghentikan impersonasi dari akun {$impersonatedUser->name}",
            [
                'admin_id' => $admin->id,
                'target_user_id' => $impersonatedUser->id,
            ],
            $admin->id
        );

        // Re-authenticate as admin
        Auth::login($admin);
        $request->session()->save();

        return redirect()->route('super-admin.dashboard')->with('status', 'Kembali ke sesi Administrator.');
    }
}
