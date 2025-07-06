@extends('layouts.login')

@section('title', 'Login')
@section('form_title', 'Login')

@section('content')
<script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<form method="POST" action="{{ route('login') }}">
    @csrf

    {{-- Notifikasi error login --}}
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops…',
                text: @json(session('error')),   // aman dari quote collision
            });
        </script>
    @endif

    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input  type="text"               {{-- <input type="username"> tidak valid di HTML5 --}}
                id="username"
                name="username"
                value="{{ old('username') }}"
                class="form-control"
                required autofocus>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input  type="password"
                id="password"
                name="password"
                class="form-control"
                required>
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" id="remember" class="form-check-input">
        <label class="form-check-label" for="remember">Ingat saya</label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>

    {{-- Tampilkan link daftar HANYA jika route register tersedia --}}
    @if (Route::has('register'))
        <p class="mt-3 text-center">
            Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
        </p>
    @endif
</form>
@endsection
