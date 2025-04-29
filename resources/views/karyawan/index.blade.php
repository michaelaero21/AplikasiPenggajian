@extends('layouts.app')

@section('title', 'Kelola Data Karyawan')

@section('content')

<h2 class="mb-4">Kelola Data Karyawan</h2>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Pencarian dan Tambah Karyawan -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <input type="text" id="search-input" class="form-control w-50 me-2" placeholder="Cari Karyawan...">
    <a href="{{ route('karyawan.create') }}" class="btn btn-success">Tambah Karyawan</a>
</div>

<!-- Tabel Karyawan -->
<div class="table-responsive">
    <table class="table table-striped table-bordered text-center" id="karyawan-table">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Alamat</th>
                <th>Jenis Kelamin</th>
                <th>No. Telepon</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($karyawans as $karyawan)
                <tr>
                    <td>{{ $karyawan->id }}</td>
                    <td>{{ $karyawan->nama }}</td>
                    <td>{{ $karyawan->jabatan }}</td>
                    <td>{{ $karyawan->alamat_karyawan }}</td>
                    <td>{{ $karyawan->jenis_kelamin }}</td>
                    <td>{{ $karyawan->nomor_telepon }}</td>
                    <td>
                        <a href="{{ route('karyawan.edit', $karyawan->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('karyawan.destroy', $karyawan->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Belum ada data karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<script>
    document.getElementById("search-input").addEventListener("input", debounce(searchTable, 300));

    function searchTable() {
        let input = document.getElementById("search-input").value.toLowerCase();
        let rows = document.querySelectorAll("#karyawan-table tbody tr");

        rows.forEach(row => {
            let columns = row.querySelectorAll("td");
            let match = Array.from(columns).some(col => col.innerText.toLowerCase().includes(input));
            row.style.display = match ? "" : "none";
        });
    }

    function debounce(func, delay) {
        let timer;
        return function() {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, arguments), delay);
        };
    }
</script>

@endsection
