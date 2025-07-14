@extends('layouts.app')

@section('title', 'Laporan Slip Gaji')

@section('content')
<div class="container">
    <h2 class="mb-4">Laporan Slip Gaji Karyawan</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    {{-- ================= FORM FILTER & PENCARIAN ================= --}}
<form method="GET" class="mb-4">

    {{-- ===== Deret 1 : Cari nama + kategori gaji + aksi ===== --}}
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

    {{-- Pilih kategori gaji --}}
    <div class="col-md-4 col-lg-3">
        <label for="kategori_gaji" class="form-label">Pilih Tipe Kategori Gaji</label>
        <select name="kategori_gaji" id="kategori_gaji" class="form-select">
            <option value="semua"   {{ request('kategori_gaji') == 'semua'   ? 'selected' : '' }}>Semua</option>
            <option value="mingguan"{{ request('kategori_gaji') == 'mingguan'? 'selected' : '' }}>Mingguan</option>
            <option value="bulanan" {{ request('kategori_gaji') == 'bulanan' ? 'selected' : '' }}>Bulanan</option>
        </select>
    </div>

    {{-- Tombol aksi --}}
    <div class="col-md-2 col-lg-2">
        <label class="form-label d-block">&nbsp;</label> {{-- spacer --}}
        <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
    </div>
</div>

</form>


    <!-- Tabel Laporan Slip Gaji -->
    <div class="table-responsive">
       <table class="table table-striped table-bordered text-center">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Gaji Pokok</th>
            <th>Tunjangan</th>
            <th>Lembur</th>
            <th>THR</th>
            <th>Insentif</th>
            <th>Total</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $item)
        <tr data-type="main">
            <td>{{ $loop->iteration}}</td>
            <td>{{ $item['karyawan']->nama }}</td>
            <td>{{ $item['karyawan']->jabatan }}</td>
            <td>Rp {{ number_format($item['total_gaji_pokok'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_tunjangan'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_lembur'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_thr'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_insentif'], 0, ',', '.') }}</td>
            <td><strong>Rp {{ number_format($item['total_gaji'], 0, ',', '.') }}</strong></td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-secondary toggle-detail" data-id="{{ $item['karyawan']->id }}">🔽</button>
            </td>
        </tr>
        <tr id="detail-{{ $item['karyawan']->id }}" data-type="detail" style="display: none; background: #f9f9f9">
            <td colspan="10">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Gaji Pokok</th>
                            <th>Tunjangan</th>
                            <th>Lembur</th>
                            <th>THR</th>
                            <th>Insentif</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['slips'] as $slip)
                        <tr>
                            <td>{{ $slip->created_at->translatedFormat('j F Y H:i') }}</td>
                            <td>{{ ucfirst($slip->kategori_gaji) }}</td>
                            <td>Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($slip->tunjangan, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($slip->lembur, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($slip->thr, 0, ',', '.') }}</td>
                            <td>Rp {{ number_format($slip->insentif, 0, ',', '.') }}</td>
                            <td><strong>Rp {{ number_format($slip->total_dibayar, 0, ',', '.') }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8" class="text-end"><strong>Total Pengeluaran:</strong></td>
            <td colspan="2"><strong id="total-pengeluaran">Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
        </tr>
    </tfoot>
</table>

    </div>
</div>
@endsection
@push('scripts')
<script>
/* ====== BATASI INPUT CUMA HURUF (A-Z, a-z) ====== */
const searchInput = document.getElementById('search-input');

searchInput.addEventListener('keypress', function (e) {
    const char = String.fromCharCode(e.which);
    if (!/^[a-zA-Z]$/.test(char)) {
        e.preventDefault(); // blokir karakter selain huruf
    }
});

searchInput.addEventListener('input', function (e) {
    // Hapus semua karakter selain huruf
    this.value = this.value.replace(/[^a-zA-Z]/g, '');
});

/* ====== LIVE SEARCH DENGAN DEBOUNCE ====== */
searchInput.addEventListener('input', debounce(searchTable, 300)); // tetap dipanggil setelah filter

function searchTable() {
    const keyword = searchInput.value.toLowerCase();
    const rows = document.querySelectorAll('.table tbody tr');
    let currentTotal = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];

        if (row.dataset.type === 'main') {
            const cells = Array.from(row.querySelectorAll('td'));
            const match = cells.some(td => td.innerText.toLowerCase().includes(keyword));

            // tampilkan atau sembunyikan baris utama
            row.style.display = match ? '' : 'none';

            // cari baris detail setelahnya
            const detailRow = rows[i + 1];
            if (detailRow && detailRow.dataset.type === 'detail') {
                if (match) {
                    // tetap biarkan status baris detail seperti sebelumnya (jika sempat dibuka user)
                    // TIDAK dipaksa hidden di sini
                } else {
                    // sembunyikan jika baris utama disembunyikan
                    detailRow.style.display = 'none';
                }
            }

            if (match) {
                const strong = row.querySelector('td strong');
                if (strong) {
                    const angka = strong.innerText.replace(/[^\d]/g, '');
                    currentTotal += parseInt(angka || 0);
                }
            }
        }
    }

    const totalFooter = document.getElementById('total-pengeluaran');
    if (totalFooter) {
        totalFooter.innerText = 'Rp ' + currentTotal.toLocaleString('id-ID');
    }
}


/* util: debounce */
function debounce(fn, delay) {
    let timer;
    return function () {
        clearTimeout(timer);
        timer = setTimeout(() => fn.apply(this, arguments), delay);
    };
}

function toggleDetail(id) {
    const row = document.getElementById('detail-' + id);
    if (row) {
        const isHidden = row.style.display === 'none';
        row.style.display = isHidden ? 'table-row' : 'none';
    }
}
document.addEventListener('click', function (e) {
    // Tombol diklik, dan memiliki class toggle-detail
    if (e.target && e.target.classList.contains('toggle-detail')) {
        const id = e.target.getAttribute('data-id');
        const detailRow = document.getElementById('detail-' + id);
        if (detailRow) {
            detailRow.style.display = detailRow.style.display === 'none' ? 'table-row' : 'none';
        }
    }
});


</script>
@endpush
