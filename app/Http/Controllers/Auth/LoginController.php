<?php
namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function username()
    {
        return 'username';
    }
    protected function credentials(Request $request)
    {
        return [
            $this->username() => $request->get($this->username()),
            'password'        => $request->get('password'),
            'status'          => 'Aktif',        // hanya user aktif
        ];
    }
    protected function authenticated($request, $user)
{
    return $user->role === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('karyawan.dashboard');
}
    public function login(Request $request)
    {
        // dd(Auth::attempt($this->credentials($request)));
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('username', $request->username)->first();
        if ($user && $user->status !== 'Aktif') {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Akun Anda nonaktif. Silakan hubungi administrator.');
        }
        if (Auth::attempt($this->credentials($request), $request->filled('remember'))) {
            $request->session()->regenerate();              // amankan session
            return redirect()->intended('/');               // sukses
        }

        return back()
            ->withInput($request->only('username'))
            ->with('error', 'Username atau password salah.');   // <-- pesan notif
    }
    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }
    protected function redirectTo()
    {
        return auth()->user()->role === 'karyawan'
            ? route('karyawan.dashboard')
            : '/home';
    }

}
