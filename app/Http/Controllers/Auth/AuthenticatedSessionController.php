<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan halaman login.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Proses login user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        // Validasi data inputan user
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Proses autentikasi
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Regenerasi session setelah login
        $request->session()->regenerate();

        // Cek role user setelah login dan redirect sesuai role
        $user = Auth::user();
        if ($user->role === 'karyawan') {
            return redirect()->route('karyawan.dashboard'); // Arahkan ke dashboard karyawan
        }

        // Jika bukan karyawan, redirect ke halaman default admin atau lainnya
        return redirect()->intended('/home'); // Halaman admin atau halaman yang dituju
    }

    /**
     * Hapus session (logout) user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/'); // Redirect ke halaman login atau halaman lain
    }

    /**
     * Menentukan URL setelah login (redirect berdasarkan role).
     *
     * @return string
     */
    protected function redirectTo()
    {
        $user = Auth::user();
        if ($user->role === 'karyawan') {
            return route('karyawan.dashboard'); // Redirect ke dashboard karyawan
        }

        return route('admin.dashboard'); // Redirect ke dashboard admin atau lainnya
    }
}
