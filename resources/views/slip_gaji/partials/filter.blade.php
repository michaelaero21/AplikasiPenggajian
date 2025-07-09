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
                    value="{{ request('search') }}"
                    pattern="[A-Za-z\s]*" 
                    inputmode="text" 
                    oninput="this.value = this.value.replace(/[^a-zA-Z\s]/g, '');">
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
/* ====== LIVE SEARCH (debounce 300 ms) ====== */
document.getElementById('search-input')
        .addEventListener('input', debounce(searchTable, 300));

function searchTable() {
    const kw = document.getElementById('search-input').value.toLowerCase();

    /* stop kalau ada karakter selain huruf & spasi */
    if (/[^a-z\s]/.test(kw)) return;

    /* ▼ hanya pilih baris LEVEL UTAMA:   #karyawan-table > tbody > tr
       Ini melewatkan semua <tr> di tabel nested (detail).            */
    const rows = document.querySelectorAll('#karyawan-table > tbody > tr');

    rows.forEach(row => {

        /* Jika baris ini DETAIL (class detail‑row) → selalu hide saat searching
           agar tidak tercecer; header di dalam nested table tidak disentuh,
           karena baris header berada di <tbody> nested.                */
        if (row.classList.contains('detail-row')){
            row.style.display = 'none';
            return;
        }

        /* Baris MAIN: cek kecocokan kata kunci */
        const match = row.innerText.toLowerCase().includes(kw);
        row.style.display = match ? '' : 'none';

        /* Sembunyikan detail‑row pasangannya ketika baris main di‑hide */
        const id = row.dataset.id;                       // pastikan baris main punya data-id
        if (id) {
            const detail = document.getElementById('detail-' + id);
            if (detail) detail.style.display = 'none';
        }
    });
}

/* util: debounce */
function debounce(fn, delay){
    let t;
    return function(){
        clearTimeout(t);
        t = setTimeout(()=>fn.apply(this, arguments), delay);
    };
}

</script>
@endpush
