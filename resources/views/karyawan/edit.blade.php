@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h4>Edit Karyawan</h4>
        </div>
        <div class="form-edit-karyawan">
            <form id="edit-karyawan-form" action="{{ route('karyawan.update', $karyawan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Karyawan</label>
                        <input type="text" id="nama" name="nama" class="form-control" value="{{ $karyawan->nama }}" required pattern="[A-Za-z\s]+" title="Hanya boleh huruf dan spasi">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" value="{{ $karyawan->nomor_telepon }}" pattern="[0-9\-]*" oninput="formatInputPhone(this)" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <select id="jabatan" name="jabatan" class="form-control" required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach($jabatanList as $jabatan)
                                <option value="{{ $jabatan }}" {{ $karyawan->jabatan == $jabatan ? 'selected' : '' }}>{{ $jabatan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div class="form-check">
                            <input type="radio" id="laki-laki" name="jenis_kelamin" value="Laki-laki" class="form-check-input" {{ $karyawan->jenis_kelamin == 'Laki-laki' ? 'checked' : '' }} required>
                            <label for="laki-laki" class="form-check-label">Laki-laki</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="perempuan" name="jenis_kelamin" value="Perempuan" class="form-check-input" {{ $karyawan->jenis_kelamin == 'Perempuan' ? 'checked' : '' }} required>
                            <label for="perempuan" class="form-check-label">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="alamat_karyawan" class="form-label">Alamat Karyawan</label>
                        <textarea id="alamat_karyawan" name="alamat_karyawan" class="form-control" rows="2" required>{{ $karyawan->alamat_karyawan }}</textarea>
                    </div>
                </div>
                <div class="mb-3">
                    <label>Status Akun</label>
                    <select name="status" class="form-select">
                        <option value="Aktif" {{ $karyawan->user->status === 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Nonaktif" {{ $karyawan->user->status === 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
        </form>
        <form method="POST" action="{{ route('karyawan.resetPassword', $karyawan->id) }}">
            @csrf
            @method('PUT')
            <button class="btn btn-warning mt-2" type="submit" onclick="return confirm('Reset password ke username?')">Reset Password</button>
        </form>
        </div>
    </div>
</div>

<script>
    // Fungsi format nomor telepon ke format xxxx-xxxx-xxxx
    function formatPhoneNumber(phoneNumber) {
        // Hapus semua karakter selain angka
        let digits = phoneNumber.replace(/\D/g, '');
        
        // Potong maksimal 12 digit (4+4+4)
        digits = digits.substring(0, 12);
        
        let part1 = digits.substring(0, 4);
        let part2 = digits.substring(4, 8);
        let part3 = digits.substring(8, 12);
        
        if(part3) {
            return part1 + '-' + part2 + '-' + part3;
        } else if(part2) {
            return part1 + '-' + part2;
        } else if(part1) {
            return part1;
        }
        return '';
    }

    // Fungsi format saat input di field nomor telepon (live formatting)
    function formatInputPhone(input) {
        let cursorPos = input.selectionStart;
        let originalLength = input.value.length;

        input.value = formatPhoneNumber(input.value);

        // Mengatur posisi cursor agar tidak lompat-lompat aneh saat input
        let newLength = input.value.length;
        cursorPos += newLength - originalLength;
        input.setSelectionRange(cursorPos, cursorPos);
    }

    // Validasi panjang nomor telepon (minimal 10 digit, maksimal 13 digit) saat submit form
    document.getElementById('edit-karyawan-form').addEventListener('submit', function(event) {
        let phoneInput = document.getElementById('nomor_telepon');
        // Ambil hanya digit tanpa strip
        let digits = phoneInput.value.replace(/\D/g, '');

        if(digits.length < 10 || digits.length > 13) {
            alert('Nomor telepon harus terdiri dari 10 sampai 13 digit angka.');
            phoneInput.focus();
            event.preventDefault(); // cegah submit form
            return false;
        }
    });

    // Saat halaman sudah load, format nomor telepon awal yang ada di input
    document.addEventListener('DOMContentLoaded', function() {
        let phoneInput = document.getElementById('nomor_telepon');
        phoneInput.value = formatPhoneNumber(phoneInput.value);
    });
    document.addEventListener('DOMContentLoaded', () => {
    const namaInput = document.getElementById('nama');

    namaInput.addEventListener('input', () => {
        // buang seluruh karakter NON‑huruf & NON‑spasi
        namaInput.value = namaInput.value.replace(/[^A-Za-z\s]/g, '');
    });
    });
</script>
@endsection
