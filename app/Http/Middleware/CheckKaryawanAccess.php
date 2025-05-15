<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckKaryawanAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role !== 'karyawan') {
            return redirect()->route('admin.dashboard'); // Redirect ke dashboard admin jika bukan karyawan
        }

        return $next($request);
    }
}
