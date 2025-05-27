<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\SlipGaji;
use App\Models\GajiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use Carbon\Carbon;

class SlipGajiController extends Controller
{
    public function index(Request $request)
    {
        $query = SlipGaji::with(['karyawan.gajiKaryawan']);

        if ($request->kategori) {
            $query->whereHas('karyawan.gajiKaryawan', function ($q) use ($request) {
                $q->where('kategori_gaji', $request->kategori);
            });
        }

        if ($request->periode) {
            $query->where('periode', $request->periode);
        }

        if ($request->search) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%');
            });
        }

        $slipGaji = $query->latest()->get();
        $kategoriGajiList = GajiKaryawan::pluck('kategori_gaji')->unique();

        return view('slip_gaji.index', compact('slipGaji', 'kategoriGajiList'));
    }

    public function generateSlip(Karyawan $karyawan, string $periode)
    {
        // Validasi apakah data gaji tersedia
        if (!$karyawan->gajiKaryawan) {
            throw new \Exception("Data gaji belum diatur untuk karyawan: {$karyawan->nama}");
        }

        $kategori = $karyawan->gajiKaryawan->kategori_gaji;
        $range = $this->getRangeFromPeriode($periode, $kategori);

        // Hitung jumlah kehadiran
        $jumlah_hadir = Absensi::where('karyawan_id', $karyawan->id)
            ->whereBetween('tanggal', $range)
            ->where('status', 'hadir')
            ->count();

        // Hitung uang makan
        $uang_makan = $jumlah_hadir * 15000;

        // Hitung lembur
        $tarifLemburPerJam = 10000;
        $totalJamLembur = 0;

        $absensiLembur = Absensi::where('karyawan_id', $karyawan->id)
            ->whereBetween('tanggal', $range)
            ->whereNotNull('jam_pulang')
            ->get();

        foreach ($absensiLembur as $absen) {
            $jamPulang = Carbon::parse($absen->jam_pulang);
            $batasLembur = Carbon::createFromTime(18, 0, 0);

            if ($jamPulang->gt($batasLembur)) {
                $selisihJam = $batasLembur->diffInMinutes($jamPulang) / 60;
                $totalJamLembur += $selisihJam;
            }
        }

        $lembur = round($totalJamLembur * $tarifLemburPerJam);

        $gaji = GajiKaryawan::where('karyawan_id', $karyawan->id)->first();

        $data = [
            'karyawan_id'   => $karyawan->id,
            'gaji_id'       => $gaji?->id,
            'periode'       => $periode,
            'kategori_gaji' => $kategori,
            'jumlah_hadir'  => $jumlah_hadir,
            'uang_makan'    => $uang_makan,
            'lembur'        => $lembur,
            'bonus'         => 0,
            'potongan'      => 0,
            'gaji_pokok'    => $kategori === 'bulanan' ? ($gaji->gaji_pokok ?? 0) : 0,
        ];

        $data['total_dibayar'] = (
            $data['gaji_pokok'] +
            $data['uang_makan'] +
            $data['lembur'] +
            $data['bonus']
        ) - $data['potongan'];

        // Update atau buat slip gaji baru agar bisa diupdate otomatis
        $slip = SlipGaji::updateOrCreate(
            [
                'karyawan_id' => $karyawan->id,
                'periode' => $periode,
                'kategori_gaji' => $kategori,
            ],
            $data
        );

        // Generate PDF dan simpan
        $pdf = PDF::loadView('slip_gaji.pdf', compact('slip'));

        $nama_karyawan = Str::slug($slip->karyawan->nama);
        $fileName = "slip_{$nama_karyawan}_{$periode}.pdf";
        $path = "slips/{$fileName}";

        Storage::disk('public')->put($path, $pdf->output());

        $slip->update(['file_pdf' => 'storage/' . $path]);

        return $slip;
    }

    protected function getRangeFromPeriode(string $periode, string $kategori)
    {
        if (strtolower($kategori) === 'mingguan') {
            preg_match('/(\d+)-(\d+)\s([A-Za-z]+)\s(\d{4})/', $periode, $matches);
            if ($matches) {
                $start = Carbon::createFromFormat('j F Y', $matches[1] . ' ' . $matches[3] . ' ' . $matches[4]);
                $end = Carbon::createFromFormat('j F Y', $matches[2] . ' ' . $matches[3] . ' ' . $matches[4]);
                return [$start->startOfDay(), $end->endOfDay()];
            }
        } else {
            $start = Carbon::createFromFormat('F Y', $periode)->startOfMonth();
            $end = Carbon::createFromFormat('F Y', $periode)->endOfMonth();
            return [$start, $end];
        }

        return [now(), now()];
    }

    // ** Tambahan: Preview PDF slip gaji **
    public function previewPdf(SlipGaji $slipGaji)
    {
        if (!$slipGaji->file_pdf || !Storage::disk('public')->exists(str_replace('storage/', '', $slipGaji->file_pdf))) {
            abort(404, 'File PDF tidak ditemukan');
        }

        $filePath = storage_path('app/public/' . str_replace('storage/', '', $slipGaji->file_pdf));
        return response()->file($filePath);
    }

    // ** Tambahan: Download PDF slip gaji **
    public function downloadPdf(SlipGaji $slipGaji)
    {
        if (!$slipGaji->file_pdf || !Storage::disk('public')->exists(str_replace('storage/', '', $slipGaji->file_pdf))) {
            abort(404, 'File PDF tidak ditemukan');
        }

        $filePath = storage_path('app/public/' . str_replace('storage/', '', $slipGaji->file_pdf));
        return response()->download($filePath, "SlipGaji_{$slipGaji->karyawan->nama}_{$slipGaji->periode}.pdf");
    }

    // ** Tambahan: Hitung otomatis untuk semua karyawan pada periode tertentu (mingguan atau bulanan) **
    public function hitungOtomatis(Request $request)
    {
        $request->validate([
            'kategori' => 'required|in:mingguan,bulanan',
            'periode' => 'required|string',
        ]);

        $kategori = $request->kategori;
        $periode = $request->periode;

        $karyawans = Karyawan::whereHas('gajiKaryawan', function ($q) use ($kategori) {
            $q->where('kategori_gaji', $kategori);
        })->get();

        foreach ($karyawans as $karyawan) {
            $this->generateSlip($karyawan, $periode);
        }

        return redirect()->back()->with('success', "Slip gaji kategori {$kategori} periode {$periode} sudah dihitung otomatis.");
    }

    // ** Tambahan: Form manual hitung slip gaji dan preview **
    public function formManualHitung()
    {
        $karyawans = Karyawan::with('gajiKaryawan')->get();
        return view('slip_gaji.manual_hitung', compact('karyawans'));
    }

    public function prosesManualHitung(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'kategori' => 'required|in:mingguan,bulanan',
            'periode' => 'required|string',
        ]);

        $karyawan = Karyawan::findOrFail($request->karyawan_id);
        $kategori = $request->kategori;
        $periode = $request->periode;

        if ($karyawan->gajiKaryawan->kategori_gaji !== $kategori) {
            return redirect()->back()->withErrors('Kategori gaji karyawan tidak sesuai.');
        }

        $slip = $this->generateSlip($karyawan, $periode);

        return redirect()->route('slip_gaji.index')->with('success', "Slip gaji sudah dihitung untuk {$karyawan->nama} periode {$periode}");
    }
}
