<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Only add CSP for HTML responses (not JSON API responses)
        $contentType = $response->headers->get('Content-Type', '');
        if (str_contains($contentType, 'text/html')) {
            $response->headers->set(
                'Content-Security-Policy',
                "default-src 'self'; " .
                "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; " .
                "font-src 'self' https://fonts.gstatic.com; " .
                "img-src 'self' data: https:; " .
                "connect-src 'self'; " .
                "frame-ancestors 'none';"
            );
        }

        // Remove server fingerprint headers
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }
}
