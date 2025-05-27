@extends('layouts.app')

@section('title', 'Data Gaji Karyawan')

@section('content')
<div class="container">
    <h2 class="mb-4">Data Gaji Karyawan</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Pencarian dan Tambah Gaji Karyawan -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="search-input" class="form-control w-50 me-2" placeholder="Cari Karyawan...">
        <a href="{{ route('gaji.create') }}" class="btn btn-success">Tambah Gaji Karyawan</a>
    
    </div>
    <!-- Tombol Buat Slip -->
    @if($gajiKaryawan->isNotEmpty())
        <a href="{{ route('slip-gaji.index', ['karyawan' => $gajiKaryawan->first()->karyawan->id, 'periode' => date('F Y')]) }}"
            class="btn btn-secondary btn-sm mt-1">
            Buat Slip
        </a>
    @endif


    <!-- Filter Kategori Gaji -->
    <div class="mb-3">
        <label class="form-label d-block">Pilih Tipe Kategori Gaji:</label>
        <a href="{{ route('gaji.index') }}" class="btn btn-outline-primary {{ request('kategori') == null ? 'active' : '' }}">
            Semua
        </a>
        <a href="{{ route('gaji.index', ['kategori' => 'mingguan']) }}" class="btn btn-outline-primary {{ request('kategori') == 'mingguan' ? 'active' : '' }}">
            Mingguan
        </a>
        <a href="{{ route('gaji.index', ['kategori' => 'bulanan']) }}" class="btn btn-outline-primary {{ request('kategori') == 'bulanan' ? 'active' : '' }}">
            Bulanan
        </a>
    </div>

    <!-- Tabel Gaji Karyawan -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center" id="gaji-table">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama Karyawan</th>
                    <th>Jabatan</th>
                    <th>Kategori Gaji</th>
                    <th>Gaji Pokok</th>
                    <th>Uang Makan</th>
                    <th>Asuransi BPJS</th>
                    <th>Uang Transportasi</th>
                    <th>Uang Lembur</th>
                    <th>THR</th>
                    <!-- Kolom untuk marketing -->
                    <th>Tunjangan Sewa Transportasi</th>
                    <th>Tunjangan Pulsa</th>
                    <th>Insentif</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($gajiKaryawan as $gaji)
                    <tr>
                        <td>{{ $gaji->karyawan->id }}</td>
                        <td>{{ $gaji->karyawan->nama }}</td>
                        <td>{{ $gaji->karyawan->jabatan }}</td>
                        <td>{{ $gaji->kategori_gaji }}</td>
                        <td>Rp {{ number_format($gaji->gaji_pokok, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($gaji->uang_makan, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($gaji->asuransi, 2, ',', '.') }}</td>

                        @if($gaji->karyawan->jabatan == 'Marketing')
                            <!-- Tidak menampilkan uang transportasi & lembur untuk marketing -->
                            <td>-</td>
                            <td>-</td>
                        @else
                            <td>Rp {{ number_format($gaji->uang_transportasi, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($gaji->uang_lembur, 2, ',', '.') }}</td>
                        @endif

                        <td>Rp {{ number_format($gaji->thr, 2, ',', '.') }}</td>

                        @if($gaji->karyawan->jabatan == 'Marketing') 
                            <!-- Menampilkan tunjangan khusus untuk marketing -->
                            <td>Rp {{ number_format($gaji->tunjangan_sewa_transport, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($gaji->tunjangan_pulsa, 2, ',', '.') }}</td>
                            <td>Rp {{ number_format($gaji->insentif, 2, ',', '.') }}</td>
                        @else
                            <!-- Menampilkan kolom kosong untuk selain marketing -->
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        @endif

                        <td>
                            <a href="{{ route('gaji.edit', $gaji->id) }}" class="btn btn-primary btn-sm">Edit</a>
                            <form action="{{ route('gaji.destroy', $gaji->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="text-center">Belum ada data gaji karyawan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById("search-input").addEventListener("input", debounce(searchTable, 300));

    function searchTable() {
        let input = document.getElementById("search-input").value.toLowerCase();
        let rows = document.querySelectorAll("#gaji-table tbody tr");

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
