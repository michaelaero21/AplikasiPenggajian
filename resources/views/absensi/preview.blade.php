@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pratinjau Data Absensi</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('absensi.import') }}" method="POST">
        @csrf
        <input type="hidden" name="filePath" value="{{ $filePath }}">

        {{-- Pilih Karyawan jika tidak ditemukan otomatis --}}
        <div class="mb-3">
            <label for="karyawan_id" class="form-label">Pilih Karyawan:</label>
            <select name="karyawan_id" id="karyawan_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($karyawans as $k)
                    <option value="{{ $k->id }}">{{ $k->nama }}</option>
                @endforeach
            </select>
        </div>

        {{-- Tabel pratinjau absensi --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        // Menggunakan Carbon untuk mendapatkan seluruh tanggal di bulan tersebut
                        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1);
                        $endDate = $startDate->copy()->endOfMonth();
                        $dates = [];

                        // Generate seluruh tanggal dalam bulan
                        for ($date = $startDate; $date <= $endDate; $date->addDay()) {
                            $dates[] = $date->toDateString();
                        }
                    @endphp

                    @foreach($dates as $date)
                        @php
                            // Mencari absensi yang sesuai dengan tanggal ini
                            $absen = collect($previewData)->firstWhere('tanggal', $date);
                        @endphp
                        <tr>
                            <td>{{ $date }}</td>
                            <td>{{ $absen['jam_masuk'] ?? '-' }}</td>
                            <td>{{ $absen['jam_pulang'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="submit" class="btn btn-success mt-3">Import Data</button>
    </form>
</div>
@endsection
