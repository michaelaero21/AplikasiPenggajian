@extends('layouts.absensi')

@section('title', 'Absensi Diimpor: ' . $karyawan->nama)

@section('content')
<div class="table-container">
    <h3 class="mb-3">Absensi – {{ $karyawan->nama }}</h3>

    {{-- notifikasi sukses / error --}}
    @if (session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger mb-3">{{ session('error') }}</div>
    @endif

    {{-- filter bulan & tahun --}}
    <form method="GET" class="d-flex justify-content-between align-items-end mb-3 flex-wrap">
        <div>
            <label for="monthSelect">Bulan:</label>
            <select id="monthSelect" name="month" class="form-select d-inline" style="width: auto;">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $i == $month ? 'selected' : '' }}>
                        {{ date('F', strtotime("2025-$i-01")) }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label for="yearSelect">Tahun:</label>
            <select id="yearSelect" name="year" class="form-select d-inline" style="width: auto;">
                @for ($i = now()->year; $i <= now()->year + 5; $i++)
                    <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>

        <a href="{{ route('absensi.index') }}" class="btn btn-secondary mt-2">
            Kembali ke Halaman Utama
        </a>

    </form>

    {{-- tabel model “semua”, tapi cuma satu karyawan --}}
    <div class="table-responsive mb-4">
        <table class="table table-bordered table-striped table-hover table-sm">
            <thead>
                <tr>
                    <th rowspan="2">ID</th>
                    <th rowspan="2">Nama&nbsp;Karyawan</th>
                    <th colspan="{{ count($days) }}" class="text-center">Hari / Tanggal</th>
                </tr>
                <tr>
                    @foreach ($days as $d)
                        @php
                            $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                        @endphp
                        <th>{{ $date->isoFormat('dd') }}<br>{{ $d }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                <tr>
                    {{-- kolom identitas --}}
                    <td>{{ $karyawan->id }}</td>
                    <td>{{ $karyawan->nama }}</td>

                    {{-- loop setiap tanggal untuk absensi --}}
                    @foreach ($days as $d)
                        @php
                            $tanggal = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                            $absen   = $absensi->firstWhere('tanggal', $tanggal);
                            $warna   = $absen && $absen->jam_masuk && $absen->jam_pulang
                                       ? 'background-color:rgb(62,255,107); font-weight:bold;'
                                       : '';
                        @endphp
                        <td class="text-center" style="{{ $warna }}">
                            @if ($absen)
                                {{ $absen->jam_masuk }}<br>{{ $absen->jam_pulang }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tbody>
        </table>
    </div>

@endsection
