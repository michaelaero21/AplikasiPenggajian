@extends('layouts.app')

@section('title', 'Laporan Slip Gaji')

@section('content')
<div class="container">
    <h2 class="mb-4">Laporan Slip Gaji Karyawan</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Filter kategori dan rentang tanggal -->
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="kategori_gaji" class="form-select">
                <option value="semua" {{ request('kategori_gaji') == 'semua' ? 'selected' : '' }}>Semua</option>
                <option value="mingguan" {{ request('kategori_gaji') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan" {{ request('kategori_gaji') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
        </div>
        <div class="col-md-3">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
        </div>
    </form>

    <!-- Tabel Laporan Slip Gaji -->
    <div class="table-responsive">
        <table class="table table-striped table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID Karyawan</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Tanggal Slip</th>
                    <th>Gaji Pokok</th>
                    <th>Tunjangan</th>
                    <th>Lembur</th>
                    <th>THR</th>
                    <th>Insentif</th>
                    <th>Total Gaji</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $slip)
                    <tr>
                        <td>{{ $slip->karyawan->id ?? '-' }}</td>
                        <td>{{ $slip->karyawan->nama ?? '-' }}</td>
                        <td>{{ $slip->karyawan->jabatan ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($slip->created_at)->format('Y-m-d') }}</td>
                        <td>Rp {{ number_format($slip->gaji_pokok, 2, ',', '.') }}</td>
                        <td>
                            Rp {{
                                number_format(
                                    $slip->uang_makan +
                                    $slip->uang_transport +
                                    $slip->tunjangan_pulsa +
                                    $slip->tunjangan_sewa +
                                    $slip->bonus +
                                    $slip->asuransi,
                                2, ',', '.')
                            }}
                        </td>
                        <td>Rp {{ number_format($slip->lembur, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($slip->thr, 2, ',', '.') }}</td>
                        <td>Rp {{ number_format($slip->insentif, 2, ',', '.') }}</td>
                        <td><strong>Rp {{ number_format($slip->total_dibayar, 2, ',', '.') }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data slip gaji ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="9" class="text-end">Total Pengeluaran:</td>
                    <td>Rp {{ number_format($total, 2, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
