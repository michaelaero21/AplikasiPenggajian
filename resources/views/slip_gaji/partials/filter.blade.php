<?php use Carbon\Carbon; ?>
<form method="GET" class="mb-4" id="filterForm">

    {{-- ── Deret 1: Cari nama + kategori ── --}}
    <div class="row g-3 align-items-end">
        {{-- Cari nama karyawan --}}
        <div class="col-md-6 col-lg-4">
            <label for="search-input" class="form-label">Cari Nama Karyawan</label>
            <input  type="text"
                    name="search"
                    id="search-input"
                    class="form-control"
                    placeholder="Nama karyawan…"
                    value="{{ request('search') }}">
        </div>

        {{-- Pilih tipe kategori gaji --}}
        <div class="col-md-6 col-lg-4">
            <label for="kategoriSelect" class="form-label">Pilih Tipe Kategori Gaji</label>
            <select name="kategori" id="kategoriSelect" class="form-select">
                <option value="semua"    {{ $kategori === null         ? 'selected' : '' }}>Semua</option>
                <option value="mingguan" {{ $kategori === 'mingguan'   ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan"  {{ $kategori === 'bulanan'    ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>
    </div>

    {{-- ── Deret 2: Range tanggal + tombol ── --}}
    <div class="row g-3 align-items-end mt-0">
        {{-- Dari tanggal --}}
        <div class="col-md-4 col-lg-3">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date"
                   name="start_date"
                   id="start_date"
                   class="form-control"
                   value="{{ $start->format('Y-m-d') }}">
        </div>

        {{-- Sampai tanggal --}}
        <div class="col-md-4 col-lg-3">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date"
                   name="end_date"
                   id="end_date"
                   class="form-control"
                   value="{{ $end->format('Y-m-d') }}">
        </div>

        {{-- Tombol aksi --}}
        <div class="col-md-4 col-lg-3">
            <label class="form-label d-block">&nbsp;</label>
            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
        </div>
    </div>
</form>
@push('scripts')
<script>
/* ====== LIVE SEARCH DENGAN DEBOUNCE ====== */
document.getElementById('search-input')
        .addEventListener('input', debounce(searchTable, 300));

function searchTable() {
    const keyword = document
                    .getElementById('search-input')
                    .value
                    .toLowerCase();

    const rows = document.querySelectorAll('#karyawan-table tbody tr');

    rows.forEach(row => {
        const cells = Array.from(row.querySelectorAll('td'));
        const match = cells.some(td =>
            td.innerText.toLowerCase().includes(keyword)
        );
        row.style.display = match ? '' : 'none';
    });
}

/* util: debounce */
function debounce(fn, delay) {
    let timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, arguments), delay);
    };
}

</script>
@endpush