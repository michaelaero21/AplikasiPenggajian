<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Validasi input, semua field opsional kecuali jika diisi
        $request->validate([
            'name' => 'nullable|string|max:255',
            'profile_photo' => 'nullable|image|mimes:jpeg,png|max:2048',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|required_with:new_password|current_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

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

        // Simpan jika ada perubahan
        if (!empty($changedData)) {
            $user->save();
        }

        $message = empty($changedData)
            ? 'Tidak ada perubahan yang dilakukan.'
            : 'Perubahan berikut telah dilakukan: ' . implode(', ', $changedData) . '.';

        return back()->with('success', $message);
    }

    public function show()
    {
        return view('profile.account-info'); // Ganti sesuai lokasi file view info akun
    }
}
