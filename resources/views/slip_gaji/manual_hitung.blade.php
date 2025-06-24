@extends('layouts.app')
<form action="{{ route('slip_gaji.manual_proses') }}" method="POST">
    @csrf
    <label for="karyawan_id">Karyawan:</label>
    <select name="karyawan_id" id="karyawan_id" required>
        <option value="">-- Pilih Karyawan --</option>
        @foreach($karyawans as $karyawan)
            <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->gajiKaryawan?->kategori_gaji ?? 'Belum Ada' }})</option>
        @endforeach
    </select>

    <label for="kategori">Kategori Gaji:</label>
    <select name="kategori" id="kategori" required>
        <option value="mingguan">Mingguan</option>
        <option value="bulanan">Bulanan</option>
    </select>

    <label for="periode">Periode:</label>
    <input type="text" name="periode" id="periode" placeholder="Contoh: 1-7 Mei 2025 (mingguan) atau Mei 2025 (bulanan)" required>

    <button type="submit">Hitung Slip Gaji</button>
</form>
