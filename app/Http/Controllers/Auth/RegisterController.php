<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    // public function showRegistrationForm()
    // {
    //     return view('auth.register');
    // }

    // /**
    //  * Handle a registration request for the application.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function register(Request $request)
    // {
    //     // Validasi input
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8|confirmed',
    //         'role' => 'required|in:Admin,Karyawan',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()
    //                          ->withErrors($validator)
    //                          ->withInput();
    //     }

    //     // Simpan pengguna baru
    //     User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'role' => $request->role,
    //     ]);

    //     // Redirect atau login pengguna setelah registrasi
    //     return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan login.');
    // }
}
