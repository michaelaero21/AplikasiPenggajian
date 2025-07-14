@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Profil</h2>

    {{-- Notifikasi Error Khusus --}}
    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif

    {{-- Notifikasi Success --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- Foto Profil -->
        <div class="mb-3 text-center">
            @if ($user->profile_photo)
            <img src="{{ asset('storage/profile_photos/' . $user->profile_photo) }}" alt="Foto Profil" 
            class="rounded-circle" width="120" height="120" style="object-fit: cover; border: 2px solid #fff;">
            @else
                <!-- Ikon Default jika Tidak Ada Foto -->
                <i class="bi bi-person-circle" style="font-size: 120px; color: #fff;"></i>
            @endif
        </div>

        <!-- Tombol Hapus Foto Profil -->
        @if ($user->profile_photo)
        <div class="mb-3 text-center">
            <button type="submit" name="remove_profile_photo" id="remove_profile_photo" value="1" class="btn btn-danger">Hapus Foto Profil</button>
        </div>
        @endif

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

        <!-- Username (read‑only) -->
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" class="form-control" value="{{ $user->username }}" readonly>
        </div>

        <!-- Input Alamat -->
        <div class="mb-3">
            <label for="alamat" class="form-label">Alamat</label>
            <input type="alamat" name="alamat" class="form-control @error('alamat') is-invalid @enderror"
                   value="{{ old('alamat', $user->alamat) }}">
            @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

         <!-- Input Nomor Telepon -->
        <div class="mb-3">
            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
            <input
                type="text"                             
                name="nomor_telepon"
                id="nomor_telepon"  
                maxlength="20"                    
                class="form-control @error('nomor_telepon') is-invalid @enderror"
                value="{{ old('nomor_telepon', $user->nomor_telepon) }}"
                >
            @error('nomor_telepon')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>


         <!-- Password Lama -->
        <div class="mb-3">
            <label for="current_password" class="form-label">Password Lama</label>
            <div class="input-group">
                <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password"><i class="bi bi-eye-slash"></i></button>
            </div>
            @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Password Baru -->
        <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <div class="input-group">
                <input type="password" name="new_password" id="new_password" class="form-control @error('new_password') is-invalid @enderror">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password"><i class="bi bi-eye-slash"></i></button>
            </div>
            @error('new_password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Konfirmasi Password Baru -->
        <div class="mb-3">
            <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
            <div class="input-group">
                <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control @error('new_password_confirmation') is-invalid @enderror">
                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password_confirmation"><i class="bi bi-eye-slash"></i></button>
            </div>
            @error('new_password_confirmation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <!-- Simpan Perubahan -->
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('profile.show') }}" class="btn btn-secondary">Batal</a>
       </div>
    </form>
</div>

<script>
   // ───────────────────────── Toggle semua password field
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const field    = document.getElementById(targetId);
            const icon     = this.querySelector('i');
            const hidden   = field.type === 'password';
            field.type = hidden ? 'text' : 'password';
            icon.classList.toggle('bi-eye', hidden);
            icon.classList.toggle('bi-eye-slash', !hidden);
        });
    });

    // ───── Formatter nomor telepon 4-4-sisa (maks 13 digit)
    function formatPhoneNumberInput(val) {
        const d = val.replace(/\D/g, '').substring(0, 13);
        if (d.length <= 4) return d;
        if (d.length <= 8) return d.replace(/(\d{4})(\d+)/, '$1-$2');
        return d.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3');
    }

    function formatWithCaret(el) {
        let digitsBefore = 0;
        for (let i = 0; i < el.selectionStart; i++) if (/\d/.test(el.value[i])) digitsBefore++;
        el.value = formatPhoneNumberInput(el.value);
        let pos = 0, seen = 0;
        while (pos < el.value.length && seen < digitsBefore) { if (/\d/.test(el.value[pos])) seen++; pos++; }
        el.setSelectionRange(pos, pos);
    }

    const phone = document.getElementById('nomor_telepon');
    if (phone) {
        phone.value = formatPhoneNumberInput(phone.value);
        phone.addEventListener('input', () => formatWithCaret(phone));
    }
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.alert .btn-close').forEach(function (button) {
            button.addEventListener('click', function () {
                let alert = button.closest('.alert');
                if (alert) {
                    alert.style.display = 'none';
                }
            });
        });
    });
});
</script>

@endsection
