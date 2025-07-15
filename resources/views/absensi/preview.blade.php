@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pratinjau Data Absensi</h3>

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

                @foreach($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}">{{ $karyawan->nama }}</option>
                @endforeach

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

        <button type="submit" id="importButton" class="btn btn-success mt-3">Import Data</button>
    </form>
</div>
@endsection
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
