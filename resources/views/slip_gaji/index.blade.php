    <?php use Carbon\Carbon; ?>
    @extends('layouts.app')

    @section('content')
    <div class="container">
        <h4>Slip Gaji</h4>

        @php
        \Carbon\Carbon::setLocale('id');

        $kategori = request('kategori');
        $rawStart = request('start_date');
        $rawEnd = request('end_date');

        // Gunakan kategori dari request jika ada
        if (!$kategori && $rawStart) {
            try {
                // Jika start_date adalah awal bulan dan end_date adalah akhir bulan, anggap bulanan
                $parsedStart = Carbon::parse($rawStart);
                $parsedEnd = $rawEnd ? Carbon::parse($rawEnd) : null;

                if ($parsedStart->isSameDay($parsedStart->copy()->startOfMonth()) &&
                    $parsedEnd && $parsedEnd->isSameDay($parsedStart->copy()->endOfMonth())) {
                    $kategori = 'bulanan';
                } else {
                    $kategori = 'mingguan';
                }
            } catch (\Exception $e) {
                $kategori = 'bulanan'; // fallback aman
            }
        }

        if (!$kategori) {
            $kategori = 'bulanan';
        }


        // Penentuan tanggal awal & akhir berdasarkan input dan kategori
        $start = $rawStart 
            ? Carbon::parse($rawStart) 
            : ($kategori === 'mingguan' 
                ? Carbon::now()->startOfWeek(Carbon::MONDAY) 
                : Carbon::now()->startOfMonth());

        $end = $rawEnd 
            ? Carbon::parse($rawEnd) 
            : ($kategori === 'mingguan' 
                ? $start->copy()->endOfWeek(Carbon::SATURDAY) 
                : $start->copy()->endOfMonth());

        $now = Carbon::now();
        if ($end->greaterThan($now)) {
            $end = $now;
        }
        // Tentukan periode sesuai kategori
        if ($kategori === 'mingguan') {
            $periode = $start ? $start->format('Y-m-d') : null;
        } elseif ($kategori === 'bulanan') {
            $periode = $start ? $start->format('Y-m') : null;
        } else {
            $periode = $start ? [$start->format('Y-m-d'), $start->format('Y-m')] : null;
        }


        if (!function_exists('formatMingguIndonesia')) {
            function formatMingguIndonesia($input) {
                try {
                    $date = Carbon::parse($input)->startOfWeek(Carbon::MONDAY);
                    $bulan = ucfirst($date->translatedFormat('F'));
                    $tahun = $date->year;
                    $firstMonday = $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
                    if ($firstMonday->month !== $date->month) $firstMonday->addWeek();
                    $mingguKe = $firstMonday->diffInWeeks($date) + 1;
                    return "Minggu ke-$mingguKe bulan $bulan $tahun";
                } catch (\Exception $e) {
                    return '-';
                }
            }
        }
    @endphp


        @if(request('start_date') && request('end_date'))
        <p><strong>Periode:</strong>
            @if($kategori === 'mingguan')
                {{ $start->translatedFormat('d M Y') }} - {{ $end->translatedFormat('d M Y') }}
            @elseif($kategori === 'bulanan')
                {{ $start->translatedFormat('F Y') }}
            @else
                {{ $start->translatedFormat('d M Y') }} - {{ $end->translatedFormat('d M Y') }}
            @endif
        </p>
    @endif


        {{-- Filter Kategori --}}
        <div class="mb-3">
            <label class="form-label d-block">Pilih Tipe Kategori Gaji:</label>
            <a href="{{ route('slip-gaji.index', array_merge(request()->except('kategori', 'start_date', 'end_date'), ['kategori' => null])) }}" 
            class="btn btn-outline-secondary {{ !request('kategori') ? 'active' : '' }}">
                Semua
            </a>
            <a href="{{ route('slip-gaji.index', array_merge(request()->except('kategori', 'start_date', 'end_date'), ['kategori' => 'bulanan'])) }}" 
            class="btn btn-outline-primary {{ request('kategori') == 'bulanan' ? 'active' : '' }}">
                Bulanan
            </a>
            <a href="{{ route('slip-gaji.index', array_merge(request()->except('kategori', 'start_date', 'end_date'), ['kategori' => 'mingguan'])) }}" 
            class="btn btn-outline-primary {{ request('kategori') == 'mingguan' ? 'active' : '' }}">
                Mingguan
            </a>
        </div>

        {{-- Filter Form --}}
<form method="GET" class="mb-3" id="filterForm">
    <div class="row g-2 align-items-end mb-2">
        <div class="col-md-4">
            <label for="search" class="form-label">Cari Nama Karyawan</label>
            <input type="text" name="search" id="search" class="form-control" placeholder="Nama karyawan..." value="{{ request('search') }}">
        </div>
    </div>

    {{-- Filter Bulanan --}}
    <div class="row g-2 align-items-end mb-2" id="bulanFilterGroup">
        <div class="col-md-4" id="bulanStartGroup">
            <label for="start_month" class="form-label">Dari Bulan</label>
            <input type="month" name="start_date" id="start_month" class="form-control" value="{{ $start->format('Y-m') }}">
        </div>
        <div class="col-md-4" id="bulanEndGroup">
            <label for="end_month" class="form-label">Sampai Bulan</label>
            <input type="month" name="end_date" id="end_month" class="form-control" value="{{ $end->format('Y-m') }}">
        </div>
    </div>

    {{-- Filter Mingguan --}}
    <div class="row g-2 align-items-end mb-2" id="mingguFilterGroup">
        <div class="col-md-4" id="mingguStartGroup">
            <label for="start_date" class="form-label">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="col-md-4" id="mingguEndGroup">
            <label for="end_date" class="form-label">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $end->format('Y-m-d') }}">
        </div>
    </div>

    <div class="row g-2">
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">Terapkan</button>
        </div>

        <div class="col-md-2 dropdown">
            <button class="btn btn-primary dropdown-toggle w-100" type="button" id="aksiDropdown" data-bs-toggle="dropdown">
                Aksi
            </button>
            <ul class="dropdown-menu" aria-labelledby="aksiDropdown">
                <li><a class="dropdown-item bulk-action" href="#" data-action="generate_massal">Generate Slip</a></li>
                <li><a class="dropdown-item bulk-action" href="#" data-action="download_massal">Unduh Slip</a></li>
                <li><a class="dropdown-item bulk-action" href="#" data-action="kirim_wa_massal">Kirim WA</a></li>
            </ul>
        </div>
    </div>
</form>


        {{-- Alert Section --}}
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

        {{-- Data Table --}}
        <form id="bulkForm" method="POST" action="">
            @csrf
            <input type="hidden" name="action" id="bulkAction">

            @php
                $kategoriTersimpan = $kategori;

                if (!$kategoriTersimpan) {
                    if (is_array($periode)) {
                        $kategoriTersimpan = 'semua';
                    } elseif (strlen($periode) === 7) {
                        $kategoriTersimpan = 'bulanan';
                    } elseif (strlen($periode) === 10) {
                        $kategoriTersimpan = 'mingguan';
                    }
                }
            @endphp

            <input type="hidden" name="kategori" value="{{ $kategoriTersimpan }}">

            @if(is_array($periode))
                <input type="hidden" name="periode[]" value="{{ $periode[0] }}">
                <input type="hidden" name="periode[]" value="{{ $periode[1] }}">
            @else
                <input type="hidden" name="periode" value="{{ $periode }}">
            @endif
            {{-- ⬇️ Tambahan ini penting untuk mendukung periode custom --}}
            <input type="hidden" name="start_date" value="{{ $start->format('Y-m-d') }}">
            <input type="hidden" name="end_date" value="{{ $end->format('Y-m-d') }}">

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
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
    </form>
                    <tbody>
                        
                    @forelse($karyawans as $karyawan)

                        @php
                            $kategoriGaji = $karyawan->gajiKaryawan->kategori_gaji ?? '-';
                        $slip = $karyawan->slipGaji->first(function($item) use ($kategori, $periode) {
                            if (!$periode) return false;

                            if ($kategori === 'bulanan') {
                                return $item->kategori_gaji === 'bulanan' && $item->periode === $periode;

                            } elseif ($kategori === 'mingguan') {
                                return $item->kategori_gaji === 'mingguan' && $item->periode === $periode;

                            } elseif ($kategori === null || $kategori === 'semua') {
                                // Saat kategori tidak dipilih, kita hanya ingin ambil slip bulanan saja
                                if (is_array($periode)) {
                                    return $item->kategori_gaji === 'bulanan' && in_array($item->periode, $periode);
                                } else {
                                    return $item->kategori_gaji === 'bulanan' && $item->periode === $periode;
                                }
                            }

                            return false;
                        });

                        @endphp
                        <tr>
                            <td><input type="checkbox" name="selected[]" value="{{ $karyawan->id }}"></td>
                            <td>{{ $karyawan->id }}</td>
                            <td>{{ $karyawan->nama }}</td>
                            <td>{{ $karyawan->jabatan ?? '-' }}</td>
                            <td>{{ ucfirst($kategoriGaji) }}</td>
                            <td>
                                @if($slip)
                                    @php
                                        $periodeCarbon = Carbon::parse($slip->periode);
                                        if ($slip->kategori_gaji === 'mingguan') {
                                            $startP = $periodeCarbon->copy()->startOfWeek(Carbon::MONDAY);
                                            $endP = $startP->copy()->addDays(5);
                                            $periodeTeks = $startP->translatedFormat('d M') . ' - ' . $endP->translatedFormat('d M Y');
                                        } else {
                                            $startP = $periodeCarbon->copy()->startOfMonth();
                                            $endP = $periodeCarbon->copy()->endOfMonth();
                                            $periodeTeks = $startP->translatedFormat('d M') . ' - ' . $endP->translatedFormat('d M Y');
                                        }
                                    @endphp
                                    {{ $periodeTeks }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $slip ? 'Rp' . number_format($slip->total_dibayar, 0, ',', '.') : '-' }}</td>
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
                                <form action="{{ route('slip-gaji.generate') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="karyawan" value="{{ $karyawan->id }}">
                                        <input type="hidden" name="kategori" value="{{ $kategori }}">
                                        @if($kategori === 'mingguan')
                                            <input type="hidden" name="periode" value="{{ is_array($periode) ? $periode[1] : $periode }}">
                                        @elseif($kategori === 'bulanan')
                                            <input type="hidden" name="periode" value="{{ is_array($periode) ? $periode[0] : $periode }}">
                                        @endif

                                        <button class="btn btn-sm btn-warning">Generate Slip</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center">Data karyawan tidak ditemukan.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </form>
    </div>
    @endsection

    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('checkAll')?.addEventListener('click', function () {
                document.querySelectorAll('input[name="selected[]"]').forEach(cb => cb.checked = this.checked);
            });
        document.addEventListener('DOMContentLoaded', () => {
        const checkAllBox = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('input[name="selected[]"]');   
            // Centang/Uncentang semua
        checkAllBox?.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        // Periksa apakah semua checkbox anak dicentang
        function updateCheckAllStatus() {
            checkAllBox.checked = [...checkboxes].every(cb => cb.checked);
        }

        // Jika salah satu checkbox diubah, update #checkAll
        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateCheckAllStatus);
        });

            document.querySelectorAll('.bulk-action').forEach(item => {
                item.addEventListener('click', function (e) {
                    e.preventDefault();
                    const action = this.dataset.action;
                    const selected = document.querySelectorAll('input[name="selected[]"]:checked');
                    if (selected.length === 0) {
                        alert('Pilih minimal satu karyawan terlebih dahulu.');
                        return;
                    }

                    const form = document.getElementById('bulkForm');
                    document.getElementById('bulkAction').value = action;

                    switch (action) {
                        case 'generate_massal':
                            form.action = "{{ route('slip-gaji.generate_massal') }}";
                            break;
                        case 'download_massal':
                            form.action = "{{ route('slip-gaji.download_massal') }}";
                            break;
                        case 'kirim_wa_massal':
                            form.action = "{{ route('slip-gaji.kirim_wa_massal') }}";
                            break;
                        default:
                            alert('Aksi tidak dikenali.');
                            return;
                    }
                    form.submit();
                });
            });

            function toggleKalenderInputs() {
                const kategori = '{{ request('kategori') }}';
                const bulanVisible = kategori === 'bulanan';
                const mingguVisible = kategori === 'mingguan';

                document.getElementById('bulanStartGroup').classList.toggle('d-none', !(bulanVisible || !kategori));
                document.getElementById('bulanEndGroup').classList.toggle('d-none', !(bulanVisible || !kategori));
                document.getElementById('mingguStartGroup').classList.toggle('d-none', !(mingguVisible || !kategori));
                document.getElementById('mingguEndGroup').classList.toggle('d-none', !(mingguVisible || !kategori));
            }

            toggleKalenderInputs();
        });
    </script>
    @endsection
