<?php

namespace App\Http\Responses;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse as PasskeyLoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class PasskeyLoginResponse implements PasskeyLoginResponseContract
{
    /**
     * Mirror the password login flow: users with 2FA are sent to the
     * challenge instead of being logged in. The guard is already logged
     * in when this response runs, so log out first, then fill the
     * challenge session the same way Fortify does.
     *
     * Passkeys auto-remember the device (30 day remember). The verify()
     * JS client never sends the remember flag, so force it on here.
     */
    public function toResponse($request): Response
    {
        /** @var Request $request */
        $user = $request->user();

        $guard = Auth::guard(config('fortify.guard', 'web'));

        if ($user instanceof User) {
            $guard->login($user, true);
        }

        if ($user instanceof User
            && Features::enabled(Features::twoFactorAuthentication())
            && $user->hasEnabledTwoFactorAuthentication()
        ) {
            $guard->logout();

            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => true,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            if ($request->wantsJson()) {
                return new JsonResponse([
                    'redirect' => route('two-factor.login'),
                    'two_factor' => true,
                ], 200);
            }

            return redirect()->route('two-factor.login');
        }

        if ($request->wantsJson()) {
            return new JsonResponse([
                'redirect' => redirect()->intended(config('passkeys.redirect', '/'))->getTargetUrl(),
            ], 200);
        }

        return redirect()->intended(config('passkeys.redirect', '/'));
    }
}
