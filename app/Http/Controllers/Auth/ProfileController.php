<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Karyawan;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }
    public function editKaryawan()
    {
        $user = Auth::user()->load('karyawan');
        return view('profile.karyawan-edit', compact('user'));
    }


    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input, semua field opsional kecuali jika diisi
        $rules = [
            'name'          => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png|max:2048',
            'email'         => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'alamat'        => 'nullable|string|max:65535',
            'nomor_telepon' => 'nullable|string|max:20',

            'current_password'          => 'nullable|required_with:new_password|current_password',
            'new_password'              => 'nullable|required_with:current_password|min:8|confirmed',
            'new_password_confirmation' => 'nullable|required_with:new_password|same:new_password',

            'remove_profile_photo' => 'nullable|boolean',
        ];

        $messages = [
            'current_password.current_password' => 'Password lama salah.',
            'new_password.min'                  => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak sama.',
            'new_password_confirmation.same'    => 'Konfirmasi password baru tidak sama.',
        ];
        $request->validate($rules, $messages);
        $changedData = [];

        // Update nama jika diisi dan berbeda
        if ($request->filled('name') && $request->name !== $user->name) {
            $user->name = $request->name;
            $changedData[] = 'Nama';
        }

        // Update email jika diisi dan berbeda
        if ($request->filled('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            $changedData[] = 'Email';
        }
        if ($request->filled('alamat') && $request->alamat !== $user->alamat) {
            $user->alamat = $request->alamat;
            $changedData[] = 'Alamat';
        }

        // ─────── UPDATE NOMOR TELEPON ───────────────────────────────
        if ($request->filled('nomor_telepon') && $request->nomor_telepon !== $user->nomor_telepon) {
            $user->nomor_telepon = $request->nomor_telepon;
            $changedData[] = 'Nomor Telepon';
        }

        // Update password jika diisi
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $changedData[] = 'Password';
            } else {
                return back()->withErrors(['current_password' => 'Password lama salah'])->withInput();
            }
        }

        // Update foto profil jika di-upload
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::delete('public/profile_photos/' . $user->profile_photo);
            }

            $file = $request->file('profile_photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $filename, 'public');
            $user->profile_photo = $filename;
            $changedData[] = 'Foto Profil';
        }

        // Hapus foto profil jika di-request
        if ($request->remove_profile_photo) {
            if ($user->profile_photo) {
                // Menghapus foto dari storage
                Storage::delete('public/profile_photos/' . $user->profile_photo);

                // Mengubah nilai profile_photo menjadi null
                $user->profile_photo = null;

                $changedData[] = 'Foto Profil dihapus';
            }
        }


        // Simpan jika ada perubahan
        if ($changed) $user->save();

        return back()->with('success', $changed
            ? 'Perubahan: '.implode(', ', $changed)
            : 'Tidak ada perubahan.');
        }

    public function show()
    {
        return view('profile.account-info'); // Ganti sesuai lokasi file view info akun
    }
     public function showKaryawan()
    {
        $user = Auth::user()->load('karyawan');
        return view('profile.karyawan-account-info', compact('user')); // Ganti sesuai lokasi file view info akun
    }
     public function updateKaryawan(Request $request)
    {
        $user = Auth::user();
        $karyawan = $user->karyawan; // pastikan ada relasi hasOne di model User

        // ─────── VALIDASI ─────────────────────────────────────
        $rules = [
            'name'          => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png|max:2048',
            'email'         => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'alamat'        => 'nullable|string|max:65535',
            'nomor_telepon' => 'nullable|string|max:20',

            'current_password'          => 'nullable|required_with:new_password|current_password',
            'new_password'              => 'nullable|required_with:current_password|min:8|confirmed',
            'new_password_confirmation' => 'nullable|required_with:new_password|same:new_password',

            'remove_profile_photo' => 'nullable|boolean',
        ];

        $messages = [
            'current_password.current_password' => 'Password lama salah.',
            'new_password.min'                  => 'Password baru minimal 8 karakter.',
            'new_password.confirmed'            => 'Konfirmasi password baru tidak sama.',
            'new_password_confirmation.same'    => 'Konfirmasi password baru tidak sama.',
        ];
        $request->validate($rules, $messages);
        $changedData = [];

        // ─────── UPDATE NAMA ──────────────────────────────────
        if ($request->filled('name') && $request->name !== $user->name) {
            $user->name = $request->name;
            if ($karyawan) $karyawan->nama = $request->name;
            $changedData[] = 'Nama';
        }

        // ─────── UPDATE EMAIL ─────────────────────────────────
        if ($request->filled('email') && $request->email !== $user->email) {
            $user->email = $request->email;
            $changedData[] = 'Email';
        }

        // ─────── UPDATE ALAMAT ───────────────────────────────
        if ($request->filled('alamat') && $request->alamat !== $user->alamat) {
            $user->alamat = $request->alamat;
            if ($karyawan) $karyawan->alamat_karyawan = $request->alamat; // kolom di tabel karyawans
            $changedData[] = 'Alamat';
        }

        // ─────── UPDATE NOMOR TELEPON ────────────────────────
        if ($request->filled('nomor_telepon') && $request->nomor_telepon !== $user->nomor_telepon) {
            $user->nomor_telepon = $request->nomor_telepon;
            if ($karyawan) $karyawan->nomor_telepon = $request->nomor_telepon;
            $changedData[] = 'Nomor Telepon';
        }

        // ─────── UPDATE PASSWORD ─────────────────────────────
        if ($request->filled('current_password') && $request->filled('new_password')) {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
                $changedData[] = 'Password';
            } else {
                return back()->withErrors(['current_password' => 'Password lama salah'])->withInput();
            }
        }

        // ─────── UPDATE FOTO PROFIL ──────────────────────────
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo) {
                Storage::delete('public/profile_photos/' . $user->profile_photo);
            }
            $file     = $request->file('profile_photo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile_photos', $filename, 'public');
            $user->profile_photo = $filename;
            $changedData[] = 'Foto Profil';
        }

        // ─────── HAPUS FOTO PROFIL ───────────────────────────
        if ($request->boolean('remove_profile_photo')) {
            if ($user->profile_photo) {
                Storage::delete('public/profile_photos/' . $user->profile_photo);
                $user->profile_photo = null;
                $changedData[] = 'Foto Profil dihapus';
            }
        }

        // ─────── SIMPAN PERUBAHAN ────────────────────────────
        if ($changed) $user->save();

        return back()->with('success', $changed
            ? 'Perubahan: '.implode(', ', $changed)
            : 'Tidak ada perubahan.');
        }
}
