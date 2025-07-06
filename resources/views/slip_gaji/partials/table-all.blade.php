<?php use Carbon\Carbon; ?>
<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle" id="karyawan-table">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Jabatan</th>
        <th>Kategori&nbsp;Gaji</th>
        <th>Periode</th>
        <th>Total&nbsp;Dibayar</th>
        <th>Status</th>
        <th>Aksi</th>
      </tr>
    </thead>

    <tbody>
@forelse ($karyawans as $karyawan)

@php
      $slip = $karyawan->slipGaji
        ->filter(function ($s) use ($kategoriFilterRaw, $periode) {
            $okKategori = !$kategoriFilterRaw
                       || $kategoriFilterRaw === 'semua'
                       || $s->kategori_gaji === $kategoriFilterRaw;

            $okPeriode  = !$periode
                       || in_array($s->periode, (array) $periode);

            return $okKategori && $okPeriode;
        })
        ->sortByDesc(function ($s) {
            /*  YYYY‑MM‑DD  ➜ 2025‑06‑23
             *  YYYY‑MM     ➜ 2025‑06‑01  (ditambah ‑01 agar comparable)
             */
            return strlen($s->periode) === 10
                ? $s->periode
                : $s->periode . '-01';
        })
        ->first();   // ← slip TERBARU secara periode
    if (!$slip) {
        $slip = (object) [
            'kategori_gaji' => null,
            'periode'       => null,
            'total_dibayar' => null,
            'status_kirim'  => null,
            'id'            => null,
        ];
    }
@endphp

      <tr data-id="{{ $karyawan->id }}">
        <td>{{ $karyawan->id }}</td>
        <td>{{ $karyawan->nama }}</td>
        <td>{{ $karyawan->jabatan ?? '-' }}</td>
        <td>{{ $slip->kategori_gaji ? ucfirst($slip->kategori_gaji) : '-' }}</td>

        {{-- PERIODE --}}
        <td>
                @if ($slip->periode)
            @if ($slip->kategori_gaji === 'mingguan')
                @php
                    $startP = Carbon::parse($slip->periode)->startOfWeek(Carbon::MONDAY);
                    $endP   = $startP->copy()->addDays(5);   // Senin–Sabtu
                @endphp
                {{ $startP->translatedFormat('d M') }} – {{ $endP->translatedFormat('d M Y') }}
            @elseif ($slip->kategori_gaji === 'bulanan')
                {{ Carbon::createFromFormat('Y-m', $slip->periode)->translatedFormat('F Y') }}
            @else
                -
            @endif
        @else
            -
        @endif
        </td>

        {{-- TOTAL --}}
        <td>
          {{ $slip->total_dibayar !== null
              ? 'Rp' . number_format($slip->total_dibayar, 0, ',', '.')
              : '-' }}
        </td>

        {{-- STATUS --}}
        <td>
          @if ($slip->status_kirim)
            <span class="badge bg-{{ $slip->status_kirim === 'terkirim' ? 'success' : 'secondary' }}">
              {{ ucfirst($slip->status_kirim) }}
            </span>
          @else
            <span class="badge bg-warning">Belum&nbsp;dibuat</span>
          @endif
        </td>

        {{-- AKSI --}}
        <td class="text-nowrap">
          @if ($slip->id)
            <a href="{{ route('slip-gaji.preview', $slip->id) }}"
               class="btn btn-sm btn-info mb-1" target="_blank">Lihat</a>

            <a href="{{ route('slip-gaji.download', $slip->id) }}"
               class="btn btn-sm btn-secondary mb-1">Unduh</a>

            <form action="{{ route('slip-gaji.kirim_wa', $slip->id) }}"
                  method="POST" class="d-inline">
              @csrf
              <button class="btn btn-sm btn-success">Kirim&nbsp;WA</button>
            </form>
          @else
            <span class="text-muted">—</span>
          @endif
        </td>
      </tr>

@empty
      <tr>
        <td colspan="8" class="text-center">Data karyawan tidak ditemukan.</td>
      </tr>
@endforelse
    </tbody>
  </table>
</div>
