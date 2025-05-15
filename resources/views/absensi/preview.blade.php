@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pratinjau Data Absensi</h3>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('absensi.import') }}" method="POST">
        @csrf
        <input type="hidden" name="filePath" value="{{ $filePath }}">

        {{-- Pilih Kategori Gaji --}}
        <div class="mb-4">
            <label class="form-label d-block">Pilih Kategori Gaji:</label>
            <div class="btn-group" role="group">
                <a href="{{ route('absensi.preview', ['kategori' => 'all', 'filePath' => $filePath]) }}" 
                    class="btn btn-outline-primary {{ $kategori_gaji === 'all' ? 'active' : '' }} ">
                    Semua
                </a>    
                <a href="{{ route('absensi.preview', ['kategori' => 'mingguan', 'filePath' => $filePath]) }}"
                    class="btn btn-outline-primary {{ $kategori_gaji === 'mingguan' ? 'active' : '' }} ">
                    Mingguan
                </a>
                <a href="{{ route('absensi.preview', ['kategori' => 'bulanan', 'filePath' => $filePath]) }}"
                    class="btn btn-outline-primary {{ $kategori_gaji === 'bulanan' ? 'active' : '' }} ">
                    Bulanan
                </a>
            </div>
        </div>

        {{-- Pilih Karyawan --}}
        <div class="mb-3">
            <label for="karyawan_id" class="form-label">Pilih Karyawan:</label>
            <select name="karyawan_id" id="karyawan_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>

                @if($kategori_gaji === 'mingguan')
                    @foreach($karyawanMingguan as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->nama }}</option>
                    @endforeach
                @elseif($kategori_gaji === 'bulanan')
                    @foreach($karyawanBulanan as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->nama }}</option>
                    @endforeach
                @elseif($kategori_gaji === 'belum_diketahui')
                    @foreach($karyawanBelumDiketahui as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->nama }}</option>
                    @endforeach
                @else
                    {{-- Jika kategori tidak diketahui, tampilkan semua --}}
                    @foreach($karyawans as $karyawan)
                        <option value="{{ $karyawan->id }}">{{ $karyawan->nama }}</option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- Tabel pratinjau absensi --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($previewData as $data)
                        <tr>
                            <td>{{ $data['tanggal'] }}</td>
                            <td>{{ $data['jam_masuk'] ?? '-' }}</td>
                            <td>{{ $data['jam_pulang'] ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">Tidak ada data absensi yang tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit" id="importButton" class="btn btn-success mt-3" onclick="disableButton()">Import Data</button>
    </form>
</div>

@section('scripts')
<script>
    function disableButton() {
        const button = document.getElementById('importButton');
        button.disabled = true;
        button.innerText = 'Sedang Mengimpor...';
    }
</script>
@endsection
@endsection
