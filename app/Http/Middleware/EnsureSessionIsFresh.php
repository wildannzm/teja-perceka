<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsFresh
{
    /**
     * Log out users whose account was deactivated after their session started.
     * Sessions created before this middleware existed have no version stored
     * and are adopted silently instead of being killed.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->hasSession()) {
            return $next($request);
        }

        $user = Auth::user();

        if ($user) {
            $version = (int) ($user->session_version ?? 0);

            if (! $request->session()->has('session_version')) {
                $request->session()->put('session_version', $version);
            } elseif ((int) $request->session()->get('session_version') !== $version) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
