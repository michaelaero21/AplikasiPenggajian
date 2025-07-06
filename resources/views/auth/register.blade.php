<!-- @extends('layouts.login')

@section('title', 'Register')
@section('form_title', 'Registrasi')

@section('content')
<form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" name="name" value="{{ old('name') }}" required class="form-control">
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required class="form-control">
    </div>

    <div class="mb-3">
        <label for="role" class="form-label">Role</label>
        <select name="role" class="form-select" required>
            <option value="">Pilih Role</option>
            <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
            <option value="Karyawan" {{ old('role') == 'Karyawan' ? 'selected' : '' }}>Karyawan</option>
        </select>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" required class="form-control">
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" required class="form-control">
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-success">Daftar</button>
    </div>

    <p class="mt-3 text-center">
        Sudah punya akun? <a href="{{ route('login') }}">Login</a>
    </p>
</form>
@endsection -->
