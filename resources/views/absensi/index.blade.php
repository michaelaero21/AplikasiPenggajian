@extends('layouts.absensi')

@section('title', 'Daftar Absensi Karyawan')

@section('content')
<div class="table-container">
    <h3 class="mb-4">Daftar Absensi Karyawan</h3>

    @if (session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="d-flex justify-content-between mb-3 flex-wrap">
        <div>
            <label for="monthSelect">Pilih Bulan:</label>
            <select id="monthSelect" class="form-select d-inline" style="width: auto;" onchange="submitFilter()">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $i == $month ? 'selected' : '' }}>
                        {{ date('F', strtotime("2025-$i-01")) }}
                    </option>
                @endfor
            </select>
        </div>

        <div>
            <label for="yearSelect">Pilih Tahun:</label>
            <select id="yearSelect" class="form-select d-inline" style="width: auto;" onchange="submitFilter()">
                @for ($i = now()->year; $i <= now()->year + 10; $i++)
                    <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Form Upload -->
    <form action="{{ route('absensi.preview') }}" method="POST" enctype="multipart/form-data" class="mb-4">
        @csrf
        <div class="mb-3">
            <label for="file" class="form-label">Upload Excel Absensi</label>
            <input class="form-control" type="file" id="file" name="file" required>
        </div>
        <button type="submit" class="btn btn-success">Upload</button>
    </form>

    <!-- Pencarian Absensi -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="search-input" class="form-control w-50 me-2" placeholder="Cari Absensi Karyawan...">
    </div>

    <form id="filterForm" method="GET" action="{{ route('absensi.show') }}">
        <input type="hidden" name="month" id="filterMonth" value="{{ $month }}">
        <input type="hidden" name="year" id="filterYear" value="{{ $year }}">
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th rowspan="2">ID</th>
                <th rowspan="2">Nama Karyawan</th>
                <th id="dateHeader" colspan="{{ count($days) }}" class="text-center">Hari / Tanggal</th>
                <th rowspan="2">Aksi</th>
            </tr>
            <tr>
                @foreach ($days as $d)
                    @php
                        $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                        $dayName = $date->isoFormat('dd');
                    @endphp
                    <th>{{ $dayName }}<br>{{ $d }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody id="absensi-table-body">
            @foreach ($karyawans as $karyawan)
                <tr>
                    <td>{{ $karyawan->id }}</td>
                    <td>{{ $karyawan->nama }}</td>
                    @foreach ($days as $d)
                        @php
                            $tanggal = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                            $absen = $karyawan->absensi->firstWhere('tanggal', $tanggal);
                        @endphp
                        <td style="text-align: center; 
                            {{ $absen && $absen->jam_masuk && $absen->jam_pulang ? 'background-color:rgb(62, 255, 107); font-weight: bold;' : 'background-color:rgb(255, 62, 78);' }}">
                            @if ($absen && $absen->jam_masuk && $absen->jam_pulang)
                                {{ $absen->jam_masuk }}<br>{{ $absen->jam_pulang }}
                            @endif
                        </td>
                    @endforeach
                    <td>
                        <form action="{{ route('absensi.deleteAll', $karyawan->id) }}" method="POST" onsubmit="return confirm('Yakin hapus semua absensi karyawan ini?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
    function submitFilter() {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;

        document.getElementById('filterMonth').value = month;
        document.getElementById('filterYear').value = year;
        document.getElementById('filterForm').submit();
    }

    document.getElementById("search-input").addEventListener("input", debounce(searchTable, 300));

    function searchTable() {
        let input = document.getElementById("search-input").value.toLowerCase();
        let rows = document.querySelectorAll("#absensi-table-body tr");

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

@push('styles')
<style>
    .status-absensi {
        color: white;
        font-weight: bold;
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
    }

    .hadir {
        background-color: green;
    }

    .izin {
        background-color: red;
    }
</style>
@endpush
