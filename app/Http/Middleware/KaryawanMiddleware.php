<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PegawaiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role === 'Karyawan') {
            return $next($request);
        }

        abort(403, 'Akses ditolak. Khusus Pegawai.');
    }
}
