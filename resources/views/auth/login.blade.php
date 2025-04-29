@extends('layouts.login')

@section('title', 'Login')
@section('form_title', 'Login')

@section('content')
<form method="POST" action="{{ route('login') }}">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control">
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" required class="form-control">
    </div>

    <div class="mb-3 form-check">
        <input type="checkbox" name="remember" class="form-check-input" id="remember">
        <label class="form-check-label" for="remember">Ingat saya</label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Login</button>
    </div>

    <p class="mt-3 text-center">
        Belum punya akun? <a href="{{ route('register') }}">Daftar</a>
    </p>
</form>
@endsection
