@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Slip Gaji</h4>

    {{-- Filter --}}
    <form method="GET" class="mb-3 row g-2">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control" placeholder="Cari nama karyawan..." value="{{ request('search') }}">
        </div>

        <div class="col-md-3">
            <select name="kategori" id="kategori" class="form-select" onchange="this.form.submit()">
                <option value="">Pilih Kategori</option>
                <option value="mingguan" {{ request('kategori') == 'mingguan' ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan" {{ request('kategori') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>

        <div class="col-md-3">
            <select name="periode" class="form-select">
                <option value="">Pilih Periode</option>
                @foreach($slipGaji->pluck('periode')->unique() as $periode)
                    <option value="{{ $periode }}" {{ request('periode') == $periode ? 'selected' : '' }}>
                        {{ $periode }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Terapkan</button>
        </div>
    </form>

    {{-- Tabel --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th><input type="checkbox" id="checkAll"></th>
                    <th>ID Karyawan</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Kategori</th>
                    <th>Periode</th>
                    <th>Total Dibayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slipGaji as $slip)
                <tr>
                    <td><input type="checkbox" name="selected[]" value="{{ $slip->id }}"></td>
                    <td>{{ $slip->karyawan->id ?? '-' }}</td>
                    <td>{{ $slip->karyawan->nama ?? '-' }}</td>
                    <td>{{ $slip->karyawan->jabatan ?? '-' }}</td>
                    <td>{{ ucfirst($slip->karyawan->gajiKaryawan->kategori_gaji ?? '-') }}</td>
                    <td>{{ $slip->periode }}</td>
                    <td>Rp{{ number_format($slip->total_dibayar, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge bg-{{ $slip->status_kirim == 'terkirim' ? 'success' : 'secondary' }}">
                            {{ ucfirst($slip->status_kirim) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('slip-gaji.preview', $slip->id) }}" class="btn btn-sm btn-info mb-1">Preview</a>
                        <a href="{{ route('slip-gaji.download', $slip->id) }}" class="btn btn-sm btn-secondary mb-1">Unduh</a>
                        <form action="{{ route('slip-gaji.kirim-wa', $slip->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success" type="submit">Kirim WA</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('checkAll').addEventListener('click', function () {
        const checkboxes = document.querySelectorAll('input[name="selected[]"]');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
