<?php use Carbon\Carbon; ?>
{{-- =========================================================
   TABEL SLIP GAJI (Mingguan) – Normal vs Laporan
   ---------------------------------------------------------
   • Normal  = range ≤ 7 hari → tampilkan slip untuk MINGGU terpilih saja
   • Laporan = range > 7 hari → baris kosong disembunyikan;
                                   Aksi Detail menampilkan riwayat slip lama
---------------------------------------------------------}}
@php
    // ───── DETEKSI MODE ─────
    $isReport = false;
    if (request('start_date') && request('end_date')) {
        $isReport = Carbon::parse(request('start_date'))
                    ->diffInDays(Carbon::parse(request('end_date'))) > 6;
    }

    // ───── PERIODE MINGGU TERPILIH (dipakai di MODE NORMAL) ─────
    //  Ambil Senin di rentang yang dipilih (mis. 2‑7 Juni → 2025‑06‑02).
    $selectedPeriode = request('start_date')
        ? Carbon::parse(request('start_date'))->startOfWeek(Carbon::MONDAY)->toDateString()
        : null;
@endphp

{{-- ===== TOOLBAR MASSAL (hanya mode normal) ===== --}}
@if(!$isReport)
    <div id="bulk-toolbar" class="alert alert-secondary d-flex justify-content-between align-items-center p-2 mb-2"
     style="display:none;">
    <span id="bulk-count"><strong>0</strong> dipilih</span>
    <span id="bulk-breakdown" class="ms-2 small text-muted">
        (0 siap generate · 0 ada slip)
    </span>
    <div class="dropdown">
        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="bulkDropdown"
                data-bs-toggle="dropdown" aria-expanded="false">
            Aksi Massal
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="bulkDropdown">
            {{-- Aksi bila belum ada slip --}}
            <li>
              <form id="bulk-generate" method="POST" action="{{ route('slip-gaji.generate_massal') }}">
                @csrf
                <input type="hidden" name="selected" value="">
                <input type="hidden" name="periode"  value="{{ $periode }}">
                <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
                <button class="dropdown-item" type="submit">Generate Slip</button>
              </form>
            </li>
            <li>
              <form id="bulk-thr" method="POST" action="{{ route('slip-gaji.setThrFlagMassal') }}">
                @csrf
                <input type="hidden" name="selected" value="">
                <input type="hidden" name="periode"  value="{{ $periode }}">
                <input type="hidden" name="kategori" value="{{ $kategori ?? '' }}">
                <button class="dropdown-item" type="submit">Input THR</button>
              </form>
            </li>
            <li><hr class="dropdown-divider"></li>
            {{-- Aksi bila sudah ada slip --}}
            <li>
              <form id="bulk-download" method="POST" action="{{ route('slip-gaji.download_massal') }}">
                @csrf
                <input type="hidden" name="slip_ids" value="">
                <button class="dropdown-item" type="submit">Unduh Slip</button>
              </form>
            </li>
            <li>
              <form id="bulk-wa" method="POST" action="{{ route('slip-gaji.kirim_wa_massal') }}">
                @csrf
                <input type="hidden" name="slip_ids" value="">
                <button class="dropdown-item" type="submit">Kirim WA</button>
              </form>
            </li>
            <!-- AKSI MASSAL HAPUS SLIP GAJI (FILE PDF DAN DATABASE) -->
            <!-- <li>
            <form id="bulk-hapus" action="{{ route('slip-gaji.hapus_massal') }}" method="POST" 
             onsubmit="return confirm('Yakin ingin menghapus semua slip yang dipilih?')">
                @csrf
                <input type="hidden" name="slip_ids" value="">
                <button type="submit" class="btn btn-danger">Hapus</button>
            </form>
            </li> -->
        </ul>
    </div>
</div>

@endif

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle" id="karyawan-table">
    <thead class="table-light text-center">
        <tr>
            @unless($isReport)
                <th class="text-center p-0" style="width:45px"><input type="checkbox" id="master" class="form-check-input m-0"></th>
            @endunless
            <th>ID</th>
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
@forelse($karyawans as $karyawan)
    @php
        // ==== Ambil semua slip mingguan karyawan ====
        $slips = $karyawan->slipGaji
         ->where('kategori_gaji','mingguan')
         ->sortBy('periode')       // ASC: 2‑7, 9‑14, 16‑21, ...
         ->values();


        if($isReport){
            // ------- MODE LAPORAN -------
            $startDate = Carbon::parse(request('start_date'))->startOfWeek(Carbon::MONDAY);
            $endDate   = Carbon::parse(request('end_date'))->endOfWeek(Carbon::SATURDAY);

            // 1. Ambil slip dlm rentang dan URUTKAN DESC (terbaru dulu)
            $filteredSlips = $slips->filter(function ($s) use ($startDate, $endDate) {
                    return Carbon::parse($s->periode)->between($startDate, $endDate);
                })
                ->sortByDesc('periode')    // ↓↓↓  30‑5, 23‑28, 16‑21, …
                ->values();

            // 2. Kolom utama  = elemen pertama (paling baru)
            $slip   = $filteredSlips->first();      // 30‑05 Jul

            // 3. Detail = sisanya (DESC sudah) — cukup slice(1)
            $others = $filteredSlips->slice(1)->values();   // 23‑28, 16‑21, 09‑14, 02‑07

            // 4. Label periode kolom utama
            $start  = $slip ? Carbon::parse($slip->periode) : null;
        
        } else {
            // ------- MODE NORMAL (≤7 hari) -------
            $slip   = $selectedPeriode ? $slips->firstWhere('periode', $selectedPeriode) : null;
            $others = collect();         // tidak ada detail‑row di mode normal
            $start  = $selectedPeriode ? Carbon::parse($selectedPeriode) : null;
        }

        // Hitung label periode (d M - d M Y) atau '-'
        if($start){
            $end   = $start->copy()->addDays(5);
            $label = $start->translatedFormat('d M') . ' - ' . $end->translatedFormat('d M Y');
        } else {
            $label = '-';
        }
    @endphp

    {{-- ================= BARIS UTAMA ================= --}}
    <tr data-id="{{ $karyawan->id }}" data-type="main"
        data-slip="{{ $slip ? 'yes' : 'no' }}"
        @if($slip) data-slipid="{{ $slip->id }}" @endif>

        @unless($isReport)
            <td class="text-center p-0"><input type="checkbox" class="sub_chk form-check-input m-0" value="{{ $karyawan->id }}"
                       data-slip="{{ $slip? 'yes':'no' }}"
                       @if($slip) data-slipid="{{ $slip->id }}" @endif></td>
        @endunless

        <td>{{ $karyawan->id }}</td>
        <td>{{ $karyawan->nama }}</td>
        <td>{{ $karyawan->jabatan ?? '-' }}</td>
        <td>{{ ucfirst($karyawan->gajiKaryawan->kategori_gaji ?? '-') }}</td>
        <td>{{ $label }}</td>
        <td>{{ $slip ? 'Rp'.number_format($slip->total_dibayar,0,',','.') : '-' }}</td>
        <td>
            @if($slip)
                <span class="badge bg-{{ $slip->status_kirim==='terkirim'?'success':'secondary' }}">{{ ucfirst($slip->status_kirim) }}</span>
            @else
                <span class="badge bg-warning">Belum dibuat</span>
            @endif
        </td>
        <td class="p-1">
            @if($slip)
                <a href="{{ route('slip-gaji.preview',$slip->id) }}" target="_blank" class="btn btn-sm btn-info mb-1">Lihat Slip</a>
                <a href="{{ route('slip-gaji.download',$slip->id) }}" class="btn btn-sm btn-secondary mb-1">Unduh</a>
                <form action="{{ route('slip-gaji.kirim_wa',$slip->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-success">Kirim WA</button>
                </form>
                <!-- BUTTON HAPUS SLIP GAJI (FILE PDF DAN DATABASE) -->
                <!-- <form action="{{ route('slip-gaji.hapus', $slip->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slip ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                </form> -->
            @elseif(!$isReport && $selectedPeriode)
                {{-- tombol Generate jika slip periode ini belum ada --}}
                <div class="d-grid gap-2">
                <form action="{{ route('slip-gaji.generate') }}" method="POST" class="m-0">
                    @csrf
                    <input type="hidden" name="karyawan" value="{{ $karyawan->id }}">
                    <input type="hidden" name="kategori"  value="mingguan">
                    <input type="hidden" name="periode"   value="{{ $periode }}">
                        @if (request('start_date'))
                        <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                    @endif
                    @if (request('end_date'))
                        <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                    @endif
                    <button class="btn btn-sm btn-warning">Generate Slip</button>
                </form>

                <form method="POST" action="{{ route('slip-gaji.setThrFlag') }}" class="m-0">
                    @csrf
                    <input type="hidden" name="karyawan_id" value="{{ $karyawan->id }}">
                    <input type="hidden" name="periode"     value="{{ $periode }}">
                    <input type="hidden" name="kategori"    value="{{ $kategori }}"> {{-- bulanan/mingguan --}}
                    <button type="submit" class="btn btn-sm btn-primary ms-2">
                        Input&nbsp;THR
                    </button>
                </form>

            @endif

            @if($isReport && $others->count())
                <button class="btn btn-sm btn-outline-secondary toggle-detail" data-id="{{ $karyawan->id }}">🔽</button>
            @endif
        </td>
    </tr>

    {{-- ===== DETAIL‑ROW (hanya mode laporan) ===== --}}
    @if($isReport && $others->count())
    <tr id="detail-{{ $karyawan->id }}" data-type="detail"
    class="detail-row"
    style="display:none;background:#f9f9f9">
        <td colspan="12" class="p-0">
            <table class="table table-sm mb-0">
            <tbody>
                {{-- header visual, sekarang di <tbody> --}}
                <tr class="table-light fw-semibold">
                    <td colspan="3"></td>
                    <td>Periode</td><td>Total Dibayar</td><td>Status</td><td>Aksi</td>
                </tr>
                <tbody>
                @foreach($others as $s)
                    @php
                        $st = Carbon::parse($s->periode);
                        $en = $st->copy()->addDays(5);
                        $lbl = $st->translatedFormat('d M') . ' - ' . $en->translatedFormat('d M Y');
                    @endphp
                    <tr>
                        <td colspan="3"></td>
                        <td>{{ $lbl }}</td>
                        <td>Rp {{ number_format($s->total_dibayar,0,',','.') }}</td>
                        <td><span class="badge bg-{{ $s->status_kirim==='terkirim'?'success':'secondary' }}">{{ ucfirst($s->status_kirim) }}</span></td>
                        <td class="p-1">
                            <a href="{{ route('slip-gaji.preview',$s->id) }}" target="_blank" class="btn btn-sm btn-info mb-1">Lihat Slip</a>
                            <a href="{{ route('slip-gaji.download',$s->id) }}" class="btn btn-sm btn-secondary mb-1">Unduh</a>
                            <form action="{{ route('slip-gaji.kirim_wa',$s->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success">Kirim WA</button>
                            </form>
                            <!-- BUTTON HAPUS SLIP GAJI (FILE PDF DAN DATABASE) -->
                            <!-- <form action="{{ route('slip-gaji.hapus', $slip->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus slip ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form> -->
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </td>
    </tr>
    @endif
@empty
    <tr><td colspan="9" class="text-center">Data karyawan tidak ditemukan.</td></tr>
@endforelse
    </tbody>
</table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- ============ SCRIPT (checkbox & toggle) ============ --}}
@push('scripts')
<script>
"use strict";
function bulkFormGuard(){
    // Semua form yg ada di #bulk-toolbar
    document.querySelectorAll('#bulk-toolbar form').forEach(form => {
        form.addEventListener('submit', e => {
            // Cek input hidden mana pun
            const selected = form.querySelector('input[name="selected"]')?.value || '';
            const slipIds  = form.querySelector('input[name="slip_ids"]')?.value || '';

            // Jika dua‑duanya kosong ⇒ tidak ada pilihan
            if(selected === '' && slipIds === ''){
                e.preventDefault();                       // batalkan submit
                // --- ganti alert() dgn SweetAlert2 jika mau lebih cantik ---
                alert('Pilih setidaknya satu karyawan / slip terlebih dahulu!');
            }
        });
    });
}
// ===== checkbox master/sub (mode normal) =====
document.addEventListener('DOMContentLoaded', () => {
    const master      = document.getElementById('master');
    const subs        = Array.from(document.querySelectorAll('.sub_chk'));
    const toolbar     = document.getElementById('bulk-toolbar');
    const breakdown   = document.getElementById('bulk-breakdown');
    const countSpan   = document.getElementById('bulk-count')?.firstElementChild;
    
    bulkFormGuard();
    const setFormIds = (selector, name, ids) => {
        const input = document.querySelector(`${selector} input[name="${name}"]`);
        if (input) input.value = ids.join(',');
    };

    const updateToolbar = () => {
        const checked   = subs.filter(cb => cb.checked);
        const total     = checked.length;
        const sudahSlip = checked.filter(cb => cb.dataset.slip === 'yes').length;
        const belumSlip = total - sudahSlip;

        if(countSpan) countSpan.textContent = total;
        if(breakdown) breakdown.textContent = `(${belumSlip} siap generate · ${sudahSlip} ada slip)`;
        if(toolbar)   toolbar.style.display = total === 0 ? 'none' : 'flex';

        const karyawanIds = checked.map(cb => cb.value);
        const slipIds     = checked.filter(cb => cb.dataset.slip === 'yes')
                                   .map(cb => cb.closest('tr').dataset.slipid)
                                   .filter(Boolean);
        setFormIds('#bulk-generate', 'selected', karyawanIds);
        setFormIds('#bulk-thr',      'selected', karyawanIds);
        setFormIds('#bulk-download', 'slip_ids', slipIds);
        setFormIds('#bulk-wa',       'slip_ids', slipIds);
        setFormIds('#bulk-hapus',       'slip_ids', slipIds);
        
    };

    // hanya di mode normal
    if(master){
        master.addEventListener('change', e => {
            subs.forEach(cb => cb.checked = e.target.checked);
            updateToolbar();
        });

        subs.forEach(cb => cb.addEventListener('change', () => {
            master.checked       = subs.every(c => c.checked);
            master.indeterminate = !master.checked && subs.some(c => c.checked);
            updateToolbar();
        }));
    }
});
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

