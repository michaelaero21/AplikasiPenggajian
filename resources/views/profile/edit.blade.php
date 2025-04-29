@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Profil</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Foto Profil -->
        <div class="mb-3 text-center">
            @if ($user->profile_photo)
            <img src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="Foto Profil" 
            class="rounded-circle" width="80" height="80" style="object-fit: cover; border: 2px solid #fff;">
            @else
                <!-- Ikon Default jika Tidak Ada Foto -->
                <img src="{{ asset('images/default.png') }}" alt="Default" width="150" height="150">
            @endif
        </div>

        <!-- Input Foto Profil -->
        <div class="mb-3">
            <label for="profile_photo" class="form-label">Foto Profil</label>
            <input type="file" name="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/jpeg, image/png">
            @error('profile_photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Input Nama -->
        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name) }}">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Input Email -->
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email) }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Ganti Password -->
        <div class="mb-3">
            <label for="current_password" class="form-label">Password Lama</label>
            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror">
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <div class="input-group">
                <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password">
                <button type="button" class="btn btn-outline-secondary" id="toggle_password">
                    <i class="bi bi-eye-slash" id="eye_icon"></i>
                </button>
            </div>
            @error('new_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" name="new_password_confirmation" class="form-control @error('new_password_confirmation') is-invalid @enderror">
            @error('new_password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Simpan Perubahan -->
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<script>
    document.getElementById('toggle_password').addEventListener('click', function() {
        var passwordField = document.getElementById('new_password');
        var icon = document.getElementById('eye_icon');

        // Toggle password visibility
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            passwordField.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    });
</script>

@endsection
