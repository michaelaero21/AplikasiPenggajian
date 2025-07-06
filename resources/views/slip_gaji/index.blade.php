@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Slip Gaji</h4>

    {{-- ========== FLASH ========== --}}
    @foreach (['success','error','warning'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg==='error'?'danger':($msg==='warning'?'warning':'success') }}
                        alert-dismissible fade show mt-2">
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

<?php
    use Carbon\Carbon;
    Carbon::setLocale('id');

    /* -------------------------------------------------
     * 1. Ambil filter persis dari query‑string
     * ------------------------------------------------- */
    $kategori  = request('kategori');        // ''|mingguan|bulanan
    $rawStart  = request('start_date');
    $rawEnd    = request('end_date');

    /* -------------------------------------------------
     * 2. Hitung tanggal awal & akhir (fallback = bulan ini)
     * ------------------------------------------------- */
    $start = $rawStart ? Carbon::parse($rawStart)
                       : Carbon::now()->startOfMonth();

    $end   = $rawEnd   ? Carbon::parse($rawEnd)
                       : Carbon::now()->endOfMonth();

    if ($end->gt(Carbon::now())) $end = Carbon::now();

    /* -------------------------------------------------
     * 3. Periode helper  (opsional – kalau ingin dipakai di controller)
     *    - mingguan  → 'YYYY‑MM‑DD'
     *    - bulanan   → 'YYYY‑MM'
     *    - semua     → null (biar controller ambil sesuai range)
     * ------------------------------------------------- */
    $periode = match($kategori) {
        'mingguan' => $start->format('Y-m-d'),
        'bulanan'  => $start->format('Y-m'),
        default    => null,
    };
?>
    {{-- INFO PERIODE --}}
    @if($rawStart && $rawEnd)
        <p><strong>Periode:</strong>
            @if($kategori==='mingguan')
                {{ $start->translatedFormat('d M Y') }}
                – {{ $end->translatedFormat('d M Y') }}
            @elseif($kategori==='bulanan')
                {{ $start->translatedFormat('F Y') }}
            @else
                {{ $start->translatedFormat('d M Y') }}
                – {{ $end->translatedFormat('d M Y') }}
            @endif
        </p>
    @endif

    {{-- ========== FILTER FORM ========== --}}
    @include('slip_gaji.partials.filter')

    {{-- ========== PILIH PARTIAL ========== --}}
    @php
        $tablePartial = match($kategori) {
            'mingguan' => 'table-mingguan',
            'bulanan'  => 'table-bulanan',
            default    => 'table-all',   // '' atau null → Semua
        };
    @endphp

    {{-- ========== RENDER TABEL ========== --}}
    @include("slip_gaji.partials.$tablePartial", [
        'karyawans'      => $karyawans ?? collect(),
        'slips'          => $slips     ?? collect(),
        'kategoriFilter' => $kategori,      // dikirim kalau partial butuh
        'startDate'      => $start,
        'endDate'        => $end,
    ])
</div>
@endsection

@section('scripts')
    @include('slip_gaji.partials.scripts')
@endsection
