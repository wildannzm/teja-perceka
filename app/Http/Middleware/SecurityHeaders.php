<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (method_exists($response, 'header')) {
            $response->header('X-Content-Type-Options', 'nosniff');
            $response->header('X-Frame-Options', 'DENY');
            $response->header('X-XSS-Protection', '1; mode=block');
            $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

            $viteDev = $this->getViteDevUrl();
            $extra = $viteDev ? " {$viteDev}" : '';

            $response->header(
                'Content-Security-Policy',
                "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'{$extra} https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline'{$extra} https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' data:{$extra} https://fonts.gstatic.com; connect-src 'self' ws: wss:{$extra} https://cdn.jsdelivr.net; frame-ancestors 'none'"
            );
        }

        return $response;
    }

    private function getViteDevUrl(): string
    {
        $hot = public_path('hot');

        if (! file_exists($hot)) {
            return '';
        }

        $url = trim((string) file_get_contents($hot));

        if ($url === '' || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $host = (string) parse_url($url, PHP_URL_HOST);
        $scheme = (string) parse_url($url, PHP_URL_SCHEME);

        if ($scheme !== 'http' || ! in_array($host, ['localhost', '127.0.0.1'], true)) {
            return '';
        }

        return $url;
    }
}
