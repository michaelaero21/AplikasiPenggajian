@extends('layouts.app') {{-- Ganti jika layout-nya beda --}}

@section('title', 'Info Akun')
@section('form_title', 'Informasi Akun')

@section('content')
@php
    $user = Auth::user();

    $jabatan = match($user->role) {
        'admin' => 'Administrator',
        'staff' => 'Staf HR',
        'manager' => 'Manajer',
        default => 'Tidak diketahui'
    };
@endphp

<div class="text-center mb-4">
        @if ($user->profile_photo)
            <img src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="Foto Profil" 
            class="rounded-circle" width="120" height="120" style="object-fit: cover; border: 2px solid #fff;">
            @else
                <!-- Ikon Default jika Tidak Ada Foto -->
                <i class="bi bi-person-circle" style="font-size: 120px; color: #fff;"></i>
            @endif
    <h4 class="mt-3">{{ $user->name }}</h4>
    <p class="text-muted mb-0">{{ $user->email }}</p>
</div>

<hr>

<ul class="list-group list-group-flush">
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Nama Lengkap</strong>
        <span>{{ $user->name }}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Email</strong>
        <span>{{ $user->email }}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Jabatan</strong>
        <span>{{ $user->role ?? 'Belum ditentukan' }}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Tanggal Bergabung</strong>
        <span>{{ $user->created_at->format('d M Y') }}</span>
    </li>
</ul>

<div class="mt-4 text-center">
    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
        <i class="fa fa-edit me-1"></i> Edit Profil
    </a>
</div>
@endsection
