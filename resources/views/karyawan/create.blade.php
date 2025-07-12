@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Karyawan</h4>
        </div>
        <div class="form-tambah-karyawan">
                       {{-- Notifikasi Error Khusus --}}
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Notifikasi Success --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validasi Laravel --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('karyawan.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Karyawan</label>
                        <input type="text" id="nama" name="nama" class="form-control" required pattern="[A-Za-z\s]+" title="Hanya boleh huruf dan spasi">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" required>
                        <small id="warning" class="form-text text-danger" style="display: none;">Nomor telepon harus antara 10 hingga 13 digit angka!</small>
                        @error('nomor_telepon')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="jabatan" class="form-label">Jabatan</label>
                        <select id="jabatan" name="jabatan" class="form-control" required>
                            <option value="">-- Pilih Jabatan --</option>
                            @foreach(['Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 'Gudang', 'Helper Gudang', 'Office Girl'] as $jabatan)
                                <option value="{{ $jabatan }}">{{ $jabatan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Jenis Kelamin -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <div class="form-check">
                            <input type="radio" id="laki-laki" name="jenis_kelamin" value="Laki-laki" class="form-check-input" required>
                            <label for="laki-laki" class="form-check-label">Laki-laki</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" id="perempuan" name="jenis_kelamin" value="Perempuan" class="form-check-input" required>
                            <label for="perempuan" class="form-check-label">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label for="alamat_karyawan" class="form-label">Alamat Karyawan</label>
                        <textarea id="alamat_karyawan" name="alamat_karyawan" class="form-control" rows="2" required></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
    function formatPhoneNumber(input) {
        let raw = input.value.replace(/[^0-9]/g, ''); // Hanya angka

        if (raw.length > 13) {
            raw = raw.slice(0, 13); // Maksimal 13 digit
        }

        let formatted = '';
        if (raw.length <= 4) {
            formatted = raw;
        } else if (raw.length <= 8) {
            formatted = raw.slice(0, 4) + '-' + raw.slice(4);
        } else {
            formatted = raw.slice(0, 4) + '-' + raw.slice(4, 8) + '-' + raw.slice(8);
        }

        input.value = formatted;

        const warning = document.getElementById('warning');
        if (raw.length < 10 || raw.length > 13) {
            warning.style.display = 'block';
        } else {
            warning.style.display = 'none';
        }
    }

    document.getElementById("nomor_telepon").addEventListener("input", function (e) {
        formatPhoneNumber(e.target);
    });

    document.getElementById("nomor_telepon").addEventListener("keydown", function (e) {
        const input = e.target;
        const key = e.key;
        const cursorPos = input.selectionStart;

        if (key === "Backspace" && cursorPos > 0 && input.value[cursorPos - 1] === '-') {
            input.setSelectionRange(cursorPos - 1, cursorPos - 1);
            e.preventDefault();
        }
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
