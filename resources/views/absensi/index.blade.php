@extends('layouts.absensi')

@section('title', 'Daftar Absensi Karyawan')

@section('content')

<div class="table-container">
    <h3 class="mb-4">Daftar Absensi Karyawan</h3>

    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif

    <!-- Tampilkan nama karyawan jika hanya satu -->
    @if (($karyawans['mingguan'] ?? collect())->count() === 1 && request('tipe') === 'mingguan')
        <div class="mb-2"><strong>Karyawan:</strong> {{ $karyawans['mingguan']->first()->nama }}</div>
    @elseif (($karyawans['bulanan'] ?? collect())->count() === 1 && request('tipe') === 'bulanan')
        <div class="mb-2"><strong>Karyawan:</strong> {{ $karyawans['bulanan']->first()->nama }}</div>
    @endif

    <!-- Filter bulan dan tahun -->
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
                    <option value="{{ $i }}" {{ $i == $year ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Form upload Excel -->
<form action="{{ route('absensi.preview') }}" method="POST" enctype="multipart/form-data" class="mb-4">
    @csrf
    <div class="mb-3">
        <label for="file" class="form-label">Upload Excel Absensi</label>
        <input class="form-control" type="file" id="file" name="file" accept=".xlsx,.xls" required>
        @error('file')
            <div class="alert alert-danger mt-2">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-success">Upload</button>
</form>

    <!-- Pencarian -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <input type="text" id="search-input" class="form-control w-50 me-2" placeholder="Cari Absensi Karyawan..." pattern="[A-Za-z\s]*"
        oninput="this.value = this.value.replace(/[^A-Za-z\s]/g,'');">
    </div>

    <!-- Filter tersembunyi -->
    <form id="filterForm" method="GET" action="{{ route('absensi.show') }}">
        <input type="hidden" name="month" id="filterMonth" value="{{ $month }}">
        <input type="hidden" name="year" id="filterYear" value="{{ $year }}">
        <input type="hidden" name="tipe" id="filterTipe" value="{{ request('tipe') }}">
    </form>

    <!-- Tombol tipe tampilan -->
    <div class="mb-4">
        <label class="form-label d-block">Pilih Tipe Kategori Gaji:</label>
        <button type="button" class="btn btn-outline-primary me-2 {{ request('tipe') === 'semua' ? 'active' : '' }}"
            onclick="setTipe('semua')">Semua</button>
        <button type="button" class="btn btn-outline-primary me-2 {{ request('tipe') === 'mingguan' ? 'active' : '' }}"
            onclick="setTipe('mingguan')">Mingguan</button>
        <button type="button" class="btn btn-outline-primary {{ request('tipe') === 'bulanan' ? 'active' : '' }}"
            onclick="setTipe('bulanan')">Bulanan</button>
    </div>

    @if (request('tipe') === 'mingguan')
        @include('absensi.mingguan', [
            'karyawansMingguan' => $karyawans['mingguan'] ?? collect(),
            'year' => $year,
            'month' => $month,
            'days' => $days
        ])
    @elseif (request('tipe') === 'bulanan')
        @include('absensi.bulanan', [
            'karyawansBulanan' => $karyawans['bulanan'] ?? collect(),
            'year' => $year,
            'month' => $month,
            'days' => $days
        ])
    @elseif (request('tipe') === 'semua')
        @include('absensi.semua', [
            'karyawans' => $karyawans['semua'] ?? collect(),
            'year' => $year,
            'month' => $month,
            'days' => $days
    ])

    @else
        <div class="alert alert-info">
            Silakan pilih tipe kategori gaji <strong>Mingguan</strong> atau <strong>Bulanan</strong> untuk melihat data absensi.
        </div>
    @endif
</div>

<script>
    function submitFilter() {
        const month = document.getElementById('monthSelect').value;
        const year = document.getElementById('yearSelect').value;
        document.getElementById('filterMonth').value = month;
        document.getElementById('filterYear').value = year;
        document.getElementById('filterForm').submit();
    }

    function setTipe(tipe) {
        document.getElementById('filterTipe').value = tipe;
        submitFilter();
    }

    document.getElementById("search-input").addEventListener("input", debounce(searchTable, 300));

    function searchTable() {
        let input = document.getElementById("search-input").value.toLowerCase();
        let rows = document.querySelectorAll("#absensi-table-body tr");

        rows.forEach(row => {
            let match = row.innerText.toLowerCase().includes(input);
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

    .btn-outline-primary.active {
        background-color: #0d6efd;
        color: white;
    }
</style>
@endpush
