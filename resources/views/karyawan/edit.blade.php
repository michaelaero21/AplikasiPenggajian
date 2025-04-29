@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header bg-warning text-white">
            <h4>Edit Karyawan</h4>
        </div>
        <div class="form-edit-karyawan">
            <form action="{{ route('karyawan.update', $karyawan->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nama" class="form-label">Nama Karyawan</label>
                        <input type="text" id="nama" name="nama" class="form-control" value="{{ $karyawan->nama }}" required pattern="[A-Za-z\s]+" title="Hanya boleh huruf dan spasi">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                        <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" value="{{ $karyawan->nomor_telepon }}" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
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

                    <!-- Input Jenis Kelamin -->
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

                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('karyawan.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>
@endsection
