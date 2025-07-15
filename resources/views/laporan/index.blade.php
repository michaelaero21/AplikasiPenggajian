@extends('layouts.app')

@section('title', 'Laporan Slip Gaji')

@section('content')
<div class="container">
    <h2 class="mb-4">Laporan Slip Gaji Karyawan</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        
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
    {{-- ── Deret 2: Range tanggal + tombol ── --}}
    <div class="row g-3 align-items-end mt-0">
        {{-- Dari tanggal --}}
        <div class="col-md-4 col-lg-3">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date"
                   name="start_date"
                   id="start_date"
                   class="form-control"
                   value="{{ request('start_date') }}">
        </div>

        {{-- Sampai tanggal --}}
        <div class="col-md-4 col-lg-3">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date"
                   name="end_date"
                   id="end_date"
                   class="form-control"
                   value="{{ request('end_date') }}">
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
        <th>Periode</th>
        <th>Gaji Pokok</th>
        <th>Uang Makan</th>
        <th>Asuransi BPJS</th>
        <th>Uang Transportasi</th>
        <th>Lembur</th>
        <th>THR</th>
        <th>Tunjangan Sewa Transportasi</th>
        <th>Tunjangan Pulsa</th>
        <th>Insentif</th>
        <th>Total</th>
        <th>Aksi</th>
    </tr>
</thead>
<tbody>
    @foreach ($data as $item)
        <tr data-type="main">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item['karyawan']->nama }}</td>
            <td>{{ $item['karyawan']->jabatan }}</td>
            @php
                $firstSlip = collect($item['slips'])->first();
                $kategori  = $firstSlip->kategori_gaji ?? null;
                $periode   = '-';

                if ($kategori === 'mingguan') {
                    $minggu = Carbon\Carbon::parse($firstSlip->periode)->startOfWeek(Carbon\Carbon::MONDAY);
                    $periode = $minggu->translatedFormat('d M') . ' – ' . $minggu->copy()->addDays(5)->translatedFormat('d M Y');
                } elseif ($kategori === 'bulanan') {
                    $periode = Carbon\Carbon::createFromFormat('Y-m', $firstSlip->periode)->translatedFormat('F Y');
                }
            @endphp



            <td>{{ $periode }}</td> <!-- Tambahan baris periode -->

            <td>Rp {{ number_format($item['total_gaji_pokok'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_uang_makan'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_bpjs'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_transport'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_lembur'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_thr'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_tunjangan_sewa'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_tunjangan_pulsa'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($item['total_insentif'], 0, ',', '.') }}</td>
            <td><strong>Rp {{ number_format($item['total_dibayar'], 0, ',', '.') }}</strong></td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-secondary toggle-detail" data-id="{{ $item['karyawan']->id }}">🔽</button>
            </td>
        </tr>

        {{-- Detail --}}
        <tr id="detail-{{ $item['karyawan']->id }}" data-type="detail" style="display: none; background: #f9f9f9">
            <td colspan="15">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Periode</th>
                            <th>Kategori</th>
                            <th>Gaji Pokok</th>
                            <th>Uang Makan</th>
                            <th>BPJS</th>
                            <th>Uang Transportasi</th>
                            <th>Lembur</th>
                            <th>THR</th>
                            <th>Tunjangan Sewa Transportasi</th>
                            <th>Tunjangan Pulsa</th>
                            <th>Insentif</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($item['slips'] as $slip)
                            <tr>
                                <td>
                                    @if ($slip->kategori_gaji === 'mingguan')
                                        @php
                                            $startP = \Carbon\Carbon::parse($slip->periode)->startOfWeek(\Carbon\Carbon::MONDAY);
                                            $endP   = $startP->copy()->addDays(5);
                                        @endphp
                                        {{ $startP->translatedFormat('d M') }} – {{ $endP->translatedFormat('d M Y') }}
                                    @elseif ($slip->kategori_gaji === 'bulanan')
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $slip->periode)->translatedFormat('F Y') }}
                                    @else
                                        {{ $slip->periode }}
                                    @endif
                                </td>

                                <td>{{ ucfirst($slip->kategori_gaji) }}</td>
                                <td>Rp {{ number_format($slip->gaji_pokok, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->uang_makan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->asuransi_bpjs, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->uang_transport, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->lembur, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->thr, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->tunjangan_sewa, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($slip->tunjangan_pulsa, 0, ',', '.') }}</td>
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
        <td colspan="13" class="text-end"><strong>Total Pengeluaran:</strong></td>
        <td colspan="2"><strong id="total-pengeluaran">Rp {{ number_format($total, 0, ',', '.') }}</strong></td>
    </tr>
</tfoot>
</table>

    </div>
</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.alert .btn-close').forEach(function (button) {
            button.addEventListener('click', function () {
                let alert = button.closest('.alert');
                if (alert) {
                    alert.style.display = 'none';
                }
            });
        });
    });


</script>
@endpush
