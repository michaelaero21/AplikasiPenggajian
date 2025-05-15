@extends('layouts.app')

@section('content')
    <div class="container">
        <h3>Riwayat Absensi - {{ $karyawan->nama }}</h3>

        @if ($absensi->isEmpty())
            <p>Tidak ada data absensi untuk bulan ini.</p>
        @else
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($absensi as $item)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                            <td>{{ $item->jam_masuk ?? 'Tidak Ada' }}</td>
                            <td>{{ $item->jam_pulang ?? 'Tidak Ada' }}</td>
                            <td>{{ $item->status == 'H' ? 'Hadir' : 'Izin' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
