<!DOCTYPE html>
<html>
<head>
    <title>Slip Gaji {{ $slip->karyawan->nama }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        h2 { margin-bottom: 0; }
    </style>
</head>
<body>
    <h2>Slip Gaji</h2>
    <p><strong>ID Karyawan:</strong> {{ $slip->karyawan->id }}</p>
    <p><strong>Nama:</strong> {{ $slip->karyawan->nama }}</p>
    <p><strong>Jabatan:</strong> {{ $slip->karyawan->jabatan ?? '-' }}</p>
    <p><strong>Periode:</strong> {{ $slip->periode }}</p>
    <p><strong>Kategori Gaji:</strong> {{ ucfirst($slip->karyawan->gajiKaryawan->kategori_gaji ?? '-') }}</p>

    <table>
        <thead>
            <tr>
                <th>Deskripsi</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            {{-- Contoh data detail slip --}}
            <tr>
                <td>Gaji Pokok</td>
                <td>Rp{{ number_format($slip->gaji_pokok ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tunjangan</td>
                <td>Rp{{ number_format($slip->tunjangan ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Potongan</td>
                <td>- Rp{{ number_format($slip->potongan ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Dibayar</strong></td>
                <td><strong>Rp{{ number_format($slip->total_dibayar, 0, ',', '.') }}</strong></td>
            </tr>
        </tbody>
    </table>

    <p>Status Kirim: {{ ucfirst($slip->status_kirim) }}</p>
</body>
</html>
