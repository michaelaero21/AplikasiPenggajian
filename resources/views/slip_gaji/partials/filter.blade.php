<?php use Carbon\Carbon; ?>
<form method="GET" class="mb-3" id="filterForm">
     <div class="row g-2 align-items-end mb-3">
        {{-- Cari nama --}}
        <div class="col-md-3">
            <label for="search-input" class="form-label mb-2">Cari Nama Karyawan</label>
            <input  type="text"
                    name="search"
                    id="search-input"         
                    class="form-control"
                    placeholder="Nama karyawan…"
                    value="{{ request('search') }}">
        </div>


        {{-- Dropdown kategori --}}
        <div class="col-md-3">
            <label class="form-label d-block mb-2">Pilih Tipe Kategori Gaji:</label>
            <select name="kategori" id="kategoriSelect" class="form-select">
                <option value="semua"           {{ $kategori === null          ? 'selected' : '' }}>Semua</option>
                <option value="mingguan"   {{ $kategori === 'mingguan'    ? 'selected' : '' }}>Mingguan</option>
                <option value="bulanan"    {{ $kategori === 'bulanan'     ? 'selected' : '' }}>Bulanan</option>
            </select>
        </div>

        {{-- RANGE TANGGAL (selalu tampil & bisa custom) --}}
        <div class="col-md-3">
            <label class="form-label d-block mb-2">Dari Tanggal:</label>
            <input  type="date" name="start_date" id="start_date"
                    class="form-control"
                    value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label d-block mb-2">Sampai Tanggal:</label>
            <input  type="date" name="end_date" id="end_date"
                    class="form-control"
                    value="{{ $end->format('Y-m-d') }}">
        </div>

        {{-- Tombol Tampilkan --}}
        <div class="col-md-3">
            <label class="form-label d-block mb-2">Aksi</label>
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