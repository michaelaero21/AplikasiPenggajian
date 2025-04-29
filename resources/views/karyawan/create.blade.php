@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h4>Tambah Karyawan</h4>
        </div>
        <div class="form-tambah-karyawan">
            <!-- Menampilkan pesan error jika ada -->
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
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
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
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
@endsection
