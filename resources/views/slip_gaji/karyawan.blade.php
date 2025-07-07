@extends('layouts.karyawan')

@section('title', 'Slip Gaji Saya')

@section('content')
<h4 class="mb-3">Slip Gaji Saya</h4>
{{-- === NOTIFICATION === --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Validasi form (optional) --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <ul class="mb-0">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
{{-- === END NOTIFICATION === --}}

{{-- === FORM RENTANG PERIODE === --}}
<form method="GET" class="row gx-2 gy-1 mb-3 align-items-end">
    <div class="col-md-4">
        <label class="form-label mb-0">Dari Tanggal</label>
        <input  type="date" name="start"
                value="{{ request('start') }}"
                class="form-control">
    </div>
    <div class="col-md-4">
        <label class="form-label mb-0">Sampai Tanggal</label>
        <input  type="date" name="end"
                value="{{ request('end') }}"
                class="form-control">
    </div>
    <div class="col-md-2 d-grid">
        <button class="btn btn-primary" type="submit">Terapkan</button>
    </div>
    <div class="col-md-2 d-grid">
        <a href="{{ route('slip-gaji.karyawan') }}" class="btn btn-secondary">
            Reset
        </a>
    </div>
</form>

{{-- === TABEL SLIP === --}}
@if($slips->count())
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Periode</th>
                    <th>Kategori</th>
                    <th>Total</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($slips as $slip)
                    <tr>
                        <td>{{ $slip->periode_formatted }}</td>
                        <td>{{ ucfirst($slip->kategori_gaji) }}</td>
                        <td>Rp{{ number_format($slip->total_dibayar, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('slip-gaji.karyawan.preview', $slip->id) }}"
                               class="btn btn-sm btn-secondary" target="_blank">Lihat Slip</a>
                            <!-- <a href="{{ route('slip-gaji.karyawan.download', $slip->id) }}"
                               class="btn btn-sm btn-primary">Download</a> -->
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- pagination + pertahankan query string --}}
    {{ $slips->withQueryString()->links() }}
@else
    <p class="text-muted">Belum ada slip gaji.</p>
@endif
@endsection
