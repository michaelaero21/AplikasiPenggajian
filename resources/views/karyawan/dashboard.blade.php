@extends('layouts.karyawan')

@section('title', 'Dashboard Karyawan')

@section('content')
<div class="row">
    <!-- Sidebar -->
    <div class="col-md-3 mb-4">
        <div class="list-group">
            <a href="#absensi" class="list-group-item list-group-item-action active">Riwayat Absensi</a>
            <a href="#gaji" class="list-group-item list-group-item-action">Riwayat Gaji</a>
        </div>
    </div>

    <!-- Konten -->
    <div class="col-md-9">
        <!-- Riwayat Absensi -->
        <h4 id="absensi">Riwayat Absensi</h4>
        <div class="table-responsive mb-4">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensis as $absen)
                        <tr>
                            <td>{{ $absen->tanggal }}</td>
                            <td>{{ $absen->jam_masuk }}</td>
                            <td>{{ $absen->jam_pulang }}</td>
                            <td>{{ $absen->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Riwayat Gaji -->
        <h4 id="gaji">Riwayat Gaji</h4>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Bulan</th>
                        <th>Total Gaji</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gajis as $gaji)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($gaji->periode)->translatedFormat('F Y') }}</td>
                            <td>Rp{{ number_format($gaji->total_gaji, 0, ',', '.') }}</td>
                            <td>{{ $gaji->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data gaji.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
