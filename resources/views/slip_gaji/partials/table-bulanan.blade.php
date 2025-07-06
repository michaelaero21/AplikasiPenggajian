<?php use Carbon\Carbon; ?>
{{-- ========= TOOLBAR mengambang ========= --}}
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
                <input type="hidden" name="kategori" value="bulanan"><!-- <‑‑ fix -->
                <button class="dropdown-item" type="submit">Generate Slip</button>
              </form>
            </li>
            <li>
              <form id="bulk-thr" method="POST" action="{{ route('slip-gaji.setThrFlagMassal') }}">
                @csrf
                <input type="hidden" name="selected" value="">
                <input type="hidden" name="periode"  value="{{ $periode }}">
                <input type="hidden" name="kategori" value="bulanan"><!-- <‑‑ fix -->
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
        </ul>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle" id="karyawan-table">
        <thead class="table-light">
            <tr>
                <th style="width:45px">
                <input type="checkbox" id="master">
                </th>
                <th>ID</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Kategori Gaji</th>
                <th>Periode</th>
                <th>Total Dibayar</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($karyawans as $karyawan)
                <?php
                    $kategoriGaji = $karyawan->gajiKaryawan->kategori_gaji ?? '-';
                    $slip = $karyawan->slipGaji->first(function($item) use ($periode) {
                        return $item->kategori_gaji === 'bulanan' && $item->periode === $periode;
                    });
                ?>
                <tr data-id="{{ $karyawan->id }}"
                    data-slip="{{ $slip ? 'yes' : 'no' }}"
                    data-slipid="{{ $slip?->id }}">
                    {{-- === checkbox per baris === --}}
                     <td>
                     <input type="checkbox"
                        class="sub_chk"
                        value="{{ $karyawan->id }}"
                        data-slip="{{ $slip ? 'yes' : 'no' }}"
                        @if($slip) data-slipid="{{ $slip->id }}" @endif>
                    </td>
                    <td>{{ $karyawan->id }}</td>
                    <td>{{ $karyawan->nama }}</td>
                    <td>{{ $karyawan->jabatan ?? '-' }}</td>
                    <td>{{ ucfirst($kategoriGaji) }}</td>
                    <td>
                        @if($slip)
                            <?php
                                $periodeCarbon = Carbon::parse($slip->periode);
                                $startP        = $periodeCarbon->copy()->startOfMonth();
                                $endP          = $periodeCarbon->copy()->endOfMonth();
                                $periodeTeks   = $startP->translatedFormat('d M') . ' - ' . $endP->translatedFormat('d M Y');
                            ?>
                            {{ $periodeTeks }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $slip ? 'Rp' . number_format($slip->total_dibayar,0,',','.') : '-' }}</td>
                    <td>
                        @if($slip)
                            <span class="badge bg-{{ $slip->status_kirim === 'terkirim' ? 'success' : 'secondary' }}">
                                {{ ucfirst($slip->status_kirim) }}
                            </span>
                        @else
                            <span class="badge bg-warning">Belum dibuat</span>
                        @endif
                    </td>
                    <td>
                        @if($slip)
                            <a href="{{ route('slip-gaji.preview', $slip->id) }}" class="btn btn-sm btn-info mb-1" target="_blank">Lihat Slip</a>
                            <a href="{{ route('slip-gaji.download', $slip->id) }}" class="btn btn-sm btn-secondary mb-1">Unduh</a>
                            <form action="{{ route('slip-gaji.kirim_wa', $slip->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-sm btn-success" type="submit">Kirim WA</button>
                            </form>
                        @else
                        <div class="d-grid gap-2">
                            <form action="{{ route('slip-gaji.generate') }}" method="POST" class="m-0">
                                @csrf
                                <input type="hidden" name="karyawan" value="{{ $karyawan->id }}">
                                <input type="hidden" name="kategori"  value="bulanan">
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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">Data karyawan tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
{{-- ===== skrip Select-All (mirip video) ===== --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const master      = document.getElementById('master');
    const subs        = Array.from(document.querySelectorAll('.sub_chk'));
    const toolbar     = document.getElementById('bulk-toolbar');
    const breakdownEl = document.getElementById('bulk-breakdown');
    const countEl     = document.getElementById('bulk-count').firstElementChild;

    const setFormIds = (selector, name, ids) => {
        const input = document.querySelector(`${selector} input[name="${name}"]`);
        if (input) input.value = ids.join(',');
    };

    const updateToolbar = () => {
        const checked   = subs.filter(cb => cb.checked);
        const total     = checked.length;
        const sudahSlip = checked.filter(cb => cb.dataset.slip === 'yes').length;
        const belumSlip = total - sudahSlip;

        countEl.textContent = total;
        if (breakdownEl) {
            breakdownEl.textContent = `(${belumSlip} siap generate · ${sudahSlip} ada slip)`;
        }

        toolbar.style.display = total === 0 ? 'none' : 'flex';

        const karyawanIds = checked.map(cb => cb.value);
        const slipIds     = checked
            .filter(cb => cb.dataset.slip === 'yes')
            .map(cb => cb.closest('tr').dataset.slipid)
            .filter(Boolean);

        setFormIds('#bulk-generate', 'selected', karyawanIds);
        setFormIds('#bulk-thr',      'selected', karyawanIds);
        setFormIds('#bulk-download', 'slip_ids', slipIds);
        setFormIds('#bulk-wa',       'slip_ids', slipIds);
    };

    // master checkbox
    master.addEventListener('change', e => {
        subs.forEach(cb => cb.checked = e.target.checked);
        updateToolbar();
    });

    // sub checkbox
    subs.forEach(cb => cb.addEventListener('change', () => {
        master.checked       = subs.every(c => c.checked);
        master.indeterminate = !master.checked && subs.some(c => c.checked);
        updateToolbar();
    }));
});
</script>
@endpush
