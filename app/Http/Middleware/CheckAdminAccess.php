<?php
// app/Http/Middleware/CheckAdmin.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->role !== 'admin') {
            return redirect()->route('karyawan.dashboard'); // Redirect ke dashboard karyawan jika bukan admin
        }

        return $next($request);
    }
}
