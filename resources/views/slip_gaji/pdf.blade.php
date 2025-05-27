<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        .center {
            text-align: center;
            font-weight: bold;
        }
        table {
            width: 100%;
        }
        .label {
            width: 40%;
        }
        .right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .border {
            border-top: 1px solid #000;
            margin-top: 30px;
        }
    </style>
</head>
<body>

    <div class="center">
        <div style="font-size: 14px;">CV. ARINDRA MANDIRI</div>
        <div style="font-size: 13px;">PALEMBANG</div>
        <div style="color: blue;">SLIP GAJI KARYAWAN</div>
    </div>

    <br>

    <table>
        <tr>
            <td class="label">ID</td><td>: {{ $karyawan->id }}</td>
            <td>Nama</td><td>: {{ $karyawan->nama }}</td>
        </tr>
        <tr>
            <td class="label">Periode</td><td>: {{ $periode }}</td>
        </tr>
    </table>

    <br>

    <table>
        <tr>
            <td class="label"><strong>PENERIMAAN :</strong></td>
            <td></td>
            <td class="label"><strong>PENGELUARAN :</strong></td>
            <td></td>
        </tr>

        <tr>
            <td>Gaji Pokok</td><td>: Rp {{ number_format($gaji->gaji_pokok, 0, ',', '.') }}</td>
            <td>Pot. PPH 21</td><td>: Rp {{ number_format($gaji->pot_pph21, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Makan & Transport</td><td>: Rp {{ number_format($gaji->uang_makan, 0, ',', '.') }}</td>
            <td>Asuransi / BPJS</td><td>: Rp {{ number_format($gaji->pot_bpjs, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bonus (Absensi/Target)</td><td>: Rp {{ number_format($gaji->bonus, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Lembur</td><td>: Rp {{ number_format($gaji->lembur, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunj. Karyawan</td><td>: Rp {{ number_format($gaji->tunjangan_karyawan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Asuransi / BPJS</td><td>: Rp {{ number_format($gaji->asuransi, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>THR</td><td>: Rp {{ number_format($gaji->thr, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td><strong>TOTAL (A)</strong></td>
            <td>: <strong>Rp {{ number_format($gaji->total_penerimaan, 0, ',', '.') }}</strong></td>
            <td><strong>TOTAL (B)</strong></td>
            <td>: <strong>Rp {{ number_format($gaji->total_potongan, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td colspan="2"></td>
            <td colspan="2"><strong>TOTAL PENERIMAAN (A - B) :</strong><br>
            <strong>Rp {{ number_format($gaji->total_bersih, 0, ',', '.') }}</strong></td>
        </tr>
    </table>

    <br><br><br>

    <table>
        <tr>
            <td class="label center">YANG MENYERAHKAN</td>
            <td class="label center">YANG MENERIMA,</td>
        </tr>
        <tr><td colspan="2"><br><br><br></td></tr>
        <tr>
            <td class="center">(____________________)</td>
            <td class="center">(____________________)</td>
        </tr>
    </table>

</body>
</html>
