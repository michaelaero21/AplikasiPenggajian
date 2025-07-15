{{-- resources/views/absensi/pindah.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pindahkan Data Absensi</h3>

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

    {{-- FORM PINDAH ABSENSI --}}
    <form action="{{ route('absensi.update', $oldKaryawan->id) }}" method="POST">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year"  value="{{ $year }}">

        {{-- Pilih Karyawan Tujuan (gaya sama seperti preview) --}}
        <div class="mb-3">
            <label for="target_karyawan_id" class="form-label">Pindahkan ke Karyawan:</label>
            <select name="target_karyawan_id" id="target_karyawan_id" class="form-select" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach (\App\Models\Karyawan::orderBy('nama')->get() as $k)
                    @if ($k->id !== $oldKaryawan->id)
                        <option value="{{ $k->id }}">{{ $k->nama }}</option>
                    @endif
                @endforeach
            </select>
        </div>

        {{-- Tabel pratinjau absensi (supaya admin yakin data yang dipindah) --}}
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($absensi as $row)
                        <tr>
                            <td>{{ $row->tanggal }}</td>
                            <td>{{ $row->jam_masuk  ?? '-' }}</td>
                            <td>{{ $row->jam_pulang ?? '-' }}</td>
                            <td>{{ $row->status     ?? 'I' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada data absensi untuk periode ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <button type="submit"
                id="moveButton"
                class="btn btn-warning mt-3">
            Pindahkan Absensi
        </button>
    </form>
</div>
@endsection

@push('styles')
<style>
    /* mengikuti style status di halaman preview, bila ingin dipakai */
    .status-absensi {
        color:#fff;font-weight:bold;padding:4px 10px;border-radius:3px;display:inline-block
    }
    .hadir{background:#198754}
    .izin {background:#dc3545}
</style>
@endpush
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