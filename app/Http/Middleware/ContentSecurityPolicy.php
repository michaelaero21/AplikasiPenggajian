<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Menangani response dari request yang masuk
        $response = $next($request);

        // Mengatur header Content-Security-Policy
        $csp = "default-src 'self'; 
                script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://ajax.googleapis.com;
                style-src 'self' 'unsafe-inline' https://fonts.googleapis.com;
                img-src 'self' data:;
                font-src 'self' https://fonts.gstatic.com;
                object-src 'none';
                connect-src 'self';";

        // Menambahkan header CSP ke response
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
