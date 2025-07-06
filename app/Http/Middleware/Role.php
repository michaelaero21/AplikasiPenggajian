<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    public function handle(Request $request, Closure $next, string $expected): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(Auth::user()->role);
        $expected = strtolower($expected);

        if ($userRole !== $expected) {
            return $userRole === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('karyawan.dashboard');
        }

        return $next($request);
    }
}
