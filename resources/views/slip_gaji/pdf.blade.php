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
        </tr>

        <tr>
            <td>Gaji Pokok</td>
            <td>: Rp {{ number_format($slip->gaji_pokok ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Makan</td>
            <td>: Rp {{ number_format($slip->uang_makan ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Uang Transport</td>
            <td>: Rp {{ number_format($slip->uang_transport ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunjangan Pulsa</td>
            <td>: Rp {{ number_format($slip->tunjangan_pulsa ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunjangan Sewa</td>
            <td>: Rp {{ number_format($slip->tunjangan_sewa ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Lembur</td>
            <td>: Rp {{ number_format($slip->lembur ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bonus</td>
            <td>: Rp {{ number_format($slip->bonus ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>THR</td>
            <td>: Rp {{ number_format($slip->thr ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Insentif</td>
            <td>: Rp {{ number_format($slip->insentif ?? 0, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Asuransi/BPJS</td>
            <td>: Rp {{ number_format($slip->asuransi ?? 0, 0, ',', '.') }}</td>
        </tr>

        <tr>
            <td><strong>TOTAL DIBAYARKAN</strong></td>
            <td>: <strong>Rp {{ number_format($slip->total_dibayar ?? 0, 0, ',', '.') }}</strong></td>
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
