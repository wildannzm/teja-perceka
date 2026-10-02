<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class LoginResponse implements LoginResponseContract
{
    /**
     * Tahan redirect: tampilkan modal sukses dulu, redirect jalan setelah modal selesai.
     */
    public function toResponse($request): mixed
    {
        $next = $request->session()->get('url.intended', Fortify::redirects('login', '/dashboard'));

        return response()->view('livewire.auth.login-success', [
            'next' => $this->safeRedirect($request, $next),
            'name' => $request->user()->name,
        ]);
    }

    /**
     * Hanya izinkan redirect internal (anti open-redirect).
     */
    private function safeRedirect(Request $request, mixed $next): string
    {
        $fallback = route('dashboard', absolute: false);

        if (! is_string($next) || $next === '') {
            return $fallback;
        }

        $host = parse_url($next, PHP_URL_HOST);

        if ($host !== null && $host !== $request->getHost()) {
            return $fallback;
        }

        $path = parse_url($next, PHP_URL_PATH) ?? '';

        if (! str_starts_with($path, '/')) {
            return $fallback;
        }

        $query = parse_url($next, PHP_URL_QUERY);

        return $query ? "{$path}?{$query}" : $path;
    }
}
