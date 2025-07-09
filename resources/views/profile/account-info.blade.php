@extends('layouts.app') {{-- Ganti jika layout-nya beda --}}

@section('title', 'Info Akun')
@section('form_title', 'Informasi Akun')

@section('content')
@php
    $user = Auth::user();
    /**
    * Format nomor telepon menjadi xxxx-xxxx-xxxx.
    * - Jika argumen null/kosong → “Tidak ada data”.
    * - Mengabaikan karakter non-angka.
    */
    function formatPhone(?string $phone): string
    {
        // 1. Kosong? langsung fallback
        if (!$phone) return 'Tidak ada data';

        // 2. Ambil hanya digit
        $digits = preg_replace('/\D/', '', $phone);

        if ($digits === '') return 'Tidak ada data';

        // 3. Bentuk output:
        //    • ≤4 digit  → apa adanya
        //    • 5-8 digit → xxxx-xxxx
        //    • ≥9 digit  → xxxx-xxxx-sisanya
        $len = strlen($digits);

        if ($len <= 4)   return $digits;                                 // 123 or 1234
        if ($len <= 8)   return preg_replace('/(\d{4})(\d+)/', '$1-$2', $digits);
        /* len ≥ 9 */    return preg_replace('/(\d{4})(\d{4})(\d+)/', '$1-$2-$3', $digits);
    }
    
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
    <h4 class="mt-3">{{ $user->name ?? 'Tidak ada data' }}</h4>
    <p class="text-muted mb-0">{{ $user->username ?? 'Tidak ada data'}}</p>
</div>

<hr>

<ul class="list-group list-group-flush">
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Nama Lengkap</strong>
        <span>{{ $user->name ?? 'Tidak ada data'}}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Username</strong>
        <span>{{ $user->username ?? 'Tidak ada data'}}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Alamat</strong>
        <span>{{ $user->alamat ?? 'Tidak ada data'}}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Nomor Telepon</strong>
        <span>{{ $user->nomor_telepon ? formatPhone($user->nomor_telepon) : 'Tidak ada data' }}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Jabatan</strong>
        <span>{{ $user->role ?? 'Belum ditentukan' }}</span>
    </li>
    <li class="list-group-item d-flex justify-content-between align-items-center">
        <strong>Tanggal Bergabung</strong>
        <span>{{ $user->created_at->format('d M Y') ?? 'Tidak ada data'}}</span>
    </li>
</ul>

<div class="mt-4 text-center">
    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
        <i class="fa fa-edit me-1"></i> Edit Profil
    </a>
</div>
@endsection
