<table class="table table-bordered">
    <thead>
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Nama Karyawan</th>
            <th colspan="{{ count($days) }}" class="text-center">Hari / Tanggal</th>
            <th rowspan="2">Aksi</th>
        </tr>
        <tr>
            @foreach ($days as $d)
                @php
                    $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                @endphp
                <th>{{ $date->isoFormat('dd') }}<br>{{ $d }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody id="absensi-table-body">
        @if(isset($karyawans['Mingguan']) && $karyawans['Mingguan']->isNotEmpty())
            @foreach ($karyawans['Mingguan'] as $karyawan)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $karyawan->nama }}</td>
                    @foreach ($days as $d)
                        @php
                            $tanggal = \Carbon\Carbon::create($year, $month, $d)->toDateString();
                            $absen = $karyawan->absensi->firstWhere('tanggal', $tanggal);
                        @endphp
                        <td class="text-center"
                            style="{{ $absen && $absen->jam_masuk && $absen->jam_pulang ? 'background-color:rgb(62,255,107); font-weight:bold;' : '' }}">
                            @if ($absen)
                                {{ $absen->jam_masuk }}<br>{{ $absen->jam_pulang }}
                            @endif
                        </td>
                    @endforeach
                    <td class="text-nowrap">
                        {{-- ➡︎ Tombol EDIT ke halaman imported --}}
                        <a href="{{ route('absensi.edit', [   {{-- tetap GET untuk menampilkan halaman pindah --}}
                                'karyawan' => $karyawan->id,
                                'month'    => $month,
                                'year'     => $year
                            ]) }}"
                            class="btn btn-primary btn-sm">
                            Edit
                        </a>
                        <!-- <form action="{{ route('absensi.deleteAll', $karyawan->id) }}" method="POST"
                            onsubmit="return confirm('Yakin hapus semua absensi karyawan ini?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="month" value="{{ $month }}">
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form> -->
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="{{ count($days) + 3 }}" class="text-center">Tidak ada data untuk kategori gaji Mingguan.</td>
            </tr>
        @endif
    </tbody>
</table>
