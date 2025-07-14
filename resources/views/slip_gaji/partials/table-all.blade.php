<?php use Carbon\Carbon; ?>
<div class="table-responsive">
  <table class="table table-bordered table-hover align-middle" id="karyawan-table">
    <thead class="table-light">
      <tr>
        <th>No</th>
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
    $filteredSlips = $karyawan->slipGaji
        ->filter(function ($s) use ($kategoriFilterRaw, $periode) {
            $okKategori = !$kategoriFilterRaw
                       || $kategoriFilterRaw === 'semua'
                       || $s->kategori_gaji === $kategoriFilterRaw;

            $okPeriode  = !$periode
                       || in_array($s->periode, (array) $periode);

            return $okKategori && $okPeriode;
        })
        ->sortByDesc(function ($s) {
            return strlen($s->periode) === 10
                ? $s->periode
                : $s->periode . '-01';
        })
        ->values();
    $kategoriGaji = $karyawan->gajiKaryawan->kategori_gaji ?? '-';
    if ($filteredSlips->isNotEmpty()) {
        $slip = $filteredSlips->first();
        $others = $filteredSlips->slice(1);
    } else {
        $allSlips = $karyawan->slipGaji
            ->sortByDesc(function ($s) {
                return strlen($s->periode) === 10
                    ? $s->periode
                    : $s->periode . '-01';
            })
            ->values();

        $slip = $allSlips->first();
        $others = $allSlips->slice(1);
    }

    if (!$slip) {
        $slip = (object) [
            'kategori_gaji' => null,
            'periode'       => null,
            'total_dibayar' => null,
            'status_kirim'  => null,
            'id'            => null,
        ];
        $others = collect(); // pastikan tidak undefined
    }
@endphp
<tr data-id="{{ $karyawan->id }}">
    <td>{{ $loop->iteration  }}</td>
    <td>{{ $karyawan->nama }}</td>
    <td>{{ $karyawan->jabatan ?? '-' }}</td>
    <td>{{ $karyawan->gajiKaryawan->kategori_gaji ?? '-' }}</td>

    {{-- PERIODE --}}
    <td>
        @if ($slip?->periode)
            @if ($slip->kategori_gaji === 'mingguan')
                @php
                    $startP = Carbon::parse($slip->periode)->startOfWeek(Carbon::MONDAY);
                    $endP   = $startP->copy()->addDays(5);
                @endphp
                {{ $startP->translatedFormat('d M') }} – {{ $endP->translatedFormat('d M Y') }}
            @elseif ($slip->kategori_gaji === 'bulanan')
                {{ Carbon::createFromFormat('Y-m', $slip->periode)->translatedFormat('F Y') }}
            @else
                {{ $slip->periode }}
            @endif
        @else
            -
        @endif
    </td>

    {{-- TOTAL --}}
    <td>
        {{ $slip && $slip->total_dibayar !== null
            ? 'Rp' . number_format($slip->total_dibayar, 0, ',', '.')
            : '-' }}
    </td>

    {{-- STATUS --}}
    <td>
        @if ($slip?->status_kirim)
            <span class="badge bg-{{ $slip->status_kirim === 'terkirim' ? 'success' : 'secondary' }}">
              {{ ucfirst($slip->status_kirim) }}
            </span>
        @else
            <span class="badge bg-warning">Belum&nbsp;dibuat</span>
        @endif
    </td>

    {{-- AKSI --}}
    <td class="text-nowrap">
        @if ($slip?->id)
            <a href="{{ route('slip-gaji.preview', $slip->id) }}"
               class="btn btn-sm btn-info mb-1" target="_blank">Lihat</a>

            <a href="{{ route('slip-gaji.download', $slip->id) }}"
               class="btn btn-sm btn-secondary mb-1">Unduh</a>

            <form action="{{ route('slip-gaji.kirim_wa', $slip->id) }}"
                  method="POST" class="d-inline">
              @csrf
              <button class="btn btn-sm btn-success">Kirim&nbsp;WA</button>
            </form>
            <!-- BUTTON HAPUS (FILE PDF DAN DATABASE)-->
            <!-- <form action="{{ route('slip-gaji.hapus', $slip->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slip ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
            </form> -->
          @else
            <span class="text-muted">—</span>
          @endif
          @if ($others->count())
            <button class="btn btn-sm btn-outline-secondary toggle-detail" data-id="{{ $karyawan->id }}">🔽</button>
        @endif
        </td>
      </tr>
      {{-- DETAIL-ROW --}}
@if ($others->count())
<tr id="detail-{{ $karyawan->id }}" class="detail-row" style="display:none;background:#f9f9f9">
    <td colspan="8" class="p-0">
        <table class="table table-sm mb-0">
            <thead class="table-light fw-semibold">
                <tr><td colspan="3"></td><td>Periode</td><td>Total Dibayar</td><td>Status</td><td>Aksi</td></tr>
            </thead>
            <tbody>
            @foreach ($others as $s)
                @php
                    if ($s->kategori_gaji === 'mingguan') {
                        $st = Carbon::parse($s->periode)->startOfWeek(Carbon::MONDAY);
                        $en = $st->copy()->addDays(5);
                        $lbl = $st->translatedFormat('d M') . ' – ' . $en->translatedFormat('d M Y');
                    } elseif ($s->kategori_gaji === 'bulanan') {
                        $lbl = Carbon::createFromFormat('Y-m', $s->periode)->translatedFormat('F Y');
                    } else {
                        $lbl = $s->periode;
                    }
                @endphp
                <tr>
                    <td colspan="3"></td>
                    <td>{{ $lbl }}</td>
                    <td>Rp{{ number_format($s->total_dibayar, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge bg-{{ $s->status_kirim === 'terkirim' ? 'success' : 'secondary' }}">
                            {{ ucfirst($s->status_kirim) }}
                        </span>
                    </td>
                    <td class="text-nowrap">
                        <a href="{{ route('slip-gaji.preview', $s->id) }}" target="_blank" class="btn btn-sm btn-info mb-1">Lihat</a>
                        <a href="{{ route('slip-gaji.download', $s->id) }}" class="btn btn-sm btn-secondary mb-1">Unduh</a>
                        <form action="{{ route('slip-gaji.kirim_wa', $s->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-sm btn-success">Kirim WA</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </td>
</tr>
@endif

@empty
      <tr>
        <td colspan="8" class="text-center">Data karyawan tidak ditemukan.</td>
      </tr>
@endforelse
    </tbody>
  </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@push('scripts')
<script>
  function toggleDetail(id){
    const row = document.getElementById('detail-'+id);
    if(!row) return;

    // apakah baris ini sedang terbuka?
    const isOpen = row.style.display !== 'none' && row.style.display !== '';

    // tutup SEMUA detail
    document.querySelectorAll('.detail-row').forEach(r => r.style.display='none');

    // jika tadinya tertutup, buka; kalau sudah terbuka biarkan tertutup
    if(!isOpen){
        row.style.display = 'table-row';
    }
}

document.addEventListener('click', e => {
    const btn = e.target.closest('.toggle-detail');
    if(btn) toggleDetail(btn.dataset.id);
});

/* --- hide semua detail saat ketik di kolom cari --- */
const searchInput = document.getElementById('search-input');
if(searchInput){
    searchInput.addEventListener('input', () => {
        document.querySelectorAll('.detail-row').forEach(r=>r.style.display='none');
    });
}
if(window.jQuery?.fn?.dataTable){
    $('#karyawan-table').on('draw.dt', () => {
        document.querySelectorAll('.detail-row').forEach(r=>r.style.display='none');
    });
}
</script>
@endpush