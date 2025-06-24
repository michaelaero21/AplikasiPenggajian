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
use ZipArchive;

use Carbon\Carbon;

class SlipGajiController extends Controller
{
public function index(Request $request)
{
    $kategoriFilter = $request->input('kategori');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $periode = $request->input('periode');

    // Fallback: jika belum ada periode tapi start_date sudah dipilih
    if (!$periode && $startDate && $kategoriFilter === 'bulanan') {
        $periode = Carbon::parse($startDate)->format('Y-m');
    }

    // Normalisasi format periode
    if ($periode && $kategoriFilter) {
        try {
            if ($kategoriFilter === 'mingguan') {
                $periode = Carbon::parse($periode)->startOfWeek(Carbon::MONDAY)->toDateString();
            } elseif ($kategoriFilter === 'bulanan') {
                $periode = Carbon::createFromFormat('Y-m', $periode)->startOfMonth()->toDateString();
            }
        } catch (\Exception $e) {
            $periode = null;
        }
    }

    // Ambil range tanggal dari periode (jika valid)
    $rangeTanggal = null;
    if ($periode && $kategoriFilter) {
        try {
            $rangeTanggal = $this->getRangeFromPeriode($periode, $kategoriFilter);
        } catch (\Exception $e) {
            $rangeTanggal = null;
        }
    }

    // Ambil semua karyawan sesuai filter kategori (jika ada), dan semua slipGaji-nya
    $karyawans = Karyawan::query()
        ->when($kategoriFilter, function ($query) use ($kategoriFilter) {
            $query->whereHas('gajiKaryawan', function ($q) use ($kategoriFilter) {
                $q->where('kategori_gaji', $kategoriFilter);
            });
        })
        ->with(['gajiKaryawan', 'slipGaji']) // Ambil semua slip, filter nanti di view
        ->get();

    $kategoriGajiList = GajiKaryawan::pluck('kategori_gaji')->unique();

    return view('slip_gaji.index', compact(
        'karyawans',
        'kategoriGajiList',
        'kategoriFilter',
        'periode',
        'startDate',
        'endDate',
        'rangeTanggal'
    ));
}




 public function generateSlipFromIndex(Request $request)
{
    $id = $request->input('karyawan');
    $periode = $request->input('periode');
    $kategori = $request->input('kategori');

    if (!$id || !$periode) {
        return redirect()->route('slip-gaji.index')->with('error', 'Karyawan dan periode harus diisi.');
    }

    $karyawan = Karyawan::find($id);
    if (!$karyawan) {
        return redirect()->route('slip-gaji.index')->with('error', 'Karyawan tidak ditemukan.');
    }

    try {
        $kategori = $request->input('kategori');
        $this->generateSlip($karyawan, $periode, strtolower($kategori));

;

        return redirect()
            ->route('slip-gaji.index', ['periode' => $periode])
            ->with('success', "Slip gaji untuk {$karyawan->nama} periode {$periode} berhasil digenerate.");
    } catch (\Exception $e) {
        return redirect()->route('slip-gaji.index')->with('error', 'Gagal generate slip: ' . $e->getMessage());
    }
}



   public function generateSlip(Karyawan $karyawan, string $periode, string $kategori = null)
{
    $gaji = $karyawan->gajiKaryawan;
    if (!$gaji) {
        throw new \Exception("Data gaji belum diatur untuk karyawan: {$karyawan->nama}");
    }

    $kategori = strtolower($kategori ?: $gaji->kategori_gaji);
    $range = $this->getRangeFromPeriode($periode, $kategori);
    $isBulanan = $kategori === 'bulanan';
    $isMingguan = $kategori === 'mingguan';

    // Hitung jumlah kehadiran
    $absensiQuery = Absensi::where('karyawan_id', $karyawan->id)
        ->whereBetween('tanggal', $range)
        ->whereIn('status', ['hadir', 'H', 'HADIR']);

    // Jika mingguan, filter Senin–Sabtu saja
    if ($isMingguan) {
        $absensiQuery->whereRaw('DAYOFWEEK(tanggal) BETWEEN 2 AND 7');
    }

    $jumlah_hadir = $absensiQuery->count();

    // Komponen gaji (disesuaikan dengan kategori)
    $uang_makan_per_hari = $gaji->uang_makan ?? 0;
    $uang_lembur_per_jam = $gaji->uang_lembur ?? 0;

    $uang_transport   = $isBulanan ? ($gaji->uang_transportasi ?? 0) : 0;
    $tunjangan_pulsa  = $isBulanan ? ($gaji->tunjangan_pulsa ?? 0) : 0;
    $gaji_pokok       = $isBulanan ? ($gaji->gaji_pokok ?? 0) : 0;
    $thr              = $isBulanan ? ($gaji->thr ?? 0) : 0;
    $tunjangan_sewa   = $isBulanan ? ($gaji->tunjangan_sewa_transport ?? 0) : 0;
    $insentif         = $isBulanan ? ($gaji->insentif ?? 0) : 0;
    $asuransi         = $isBulanan ? ($gaji->asuransi ?? 0) : 0;

    $uang_makan = $jumlah_hadir * $uang_makan_per_hari;

    $totalJamLembur = 0;

    $absensiLembur = Absensi::where('karyawan_id', $karyawan->id)
        ->whereBetween('tanggal', $range)
        ->whereNotNull('jam_pulang')
        ->get();

    foreach ($absensiLembur as $absen) {
        // Gabungkan tanggal absensi dengan jam_pulang (supaya akurat)
        $jamPulang = Carbon::parse($absen->tanggal . ' ' . $absen->jam_pulang);
        $batasLembur = Carbon::parse($absen->tanggal)->setTime(18, 0, 0);

        if ($jamPulang->gt($batasLembur)) {
            $selisihMenit = $batasLembur->diffInMinutes($jamPulang);
            $jumlahJamLembur = max(1, ceil($selisihMenit / 60));
            $totalJamLembur += $jumlahJamLembur;

            logger("Tanggal {$absen->tanggal} - Jam pulang: {$jamPulang->format('Y-m-d H:i')} - Selisih menit: $selisihMenit - Lembur: $jumlahJamLembur jam");
        }
    }

    $uang_lembur_per_jam = $karyawan->gajiKaryawan->uang_lembur ?? 0;
    $lembur = $totalJamLembur * $uang_lembur_per_jam;

    logger("TOTAL JAM LEMBUR: $totalJamLembur");
    logger("TARIF PER JAM: $uang_lembur_per_jam");
    logger("TOTAL UANG LEMBUR: $lembur");

    $data = [
        'karyawan_id'      => $karyawan->id,
        'gaji_id'          => $gaji->id,
        'periode'          => $periode,
        'kategori_gaji'    => $kategori,
        'jumlah_hadir'     => $jumlah_hadir,
        'uang_makan'       => $uang_makan,
        'uang_transport'   => $uang_transport,
        'tunjangan_pulsa'  => $tunjangan_pulsa,
        'tunjangan_sewa'   => $tunjangan_sewa,
        'lembur'           => $lembur,
        'bonus'            => 0,
        'thr'              => $thr,
        'insentif'         => $insentif,
        'asuransi'         => $asuransi,
        'gaji_pokok'       => $gaji_pokok,
    ];

    $data['total_dibayar'] = array_sum([
        $data['gaji_pokok'],
        $data['uang_makan'],
        $data['uang_transport'],
        $data['tunjangan_pulsa'],
        $data['tunjangan_sewa'],
        $data['lembur'],
        $data['bonus'],
        $data['thr'],
        $data['insentif'],
    ]);

    $slip = SlipGaji::updateOrCreate(
        [
            'karyawan_id' => $karyawan->id,
            'periode' => $periode,
            'kategori_gaji' => $kategori,
        ],
        $data
    );

    $pdf = PDF::loadView('slip_gaji.pdf', [
        'slip' => $slip,
        'karyawan' => $karyawan,
        'periode' => $periode,
        'gaji' => $gaji,
    ]);

    $nama_karyawan = Str::slug($karyawan->nama);
    $fileName = "slip_{$nama_karyawan}_{$periode}_{$kategori}.pdf";
    $path = "slips/{$fileName}";

    Storage::disk('public')->put($path, $pdf->output());
    $slip->update(['file_pdf' => 'storage/' . $path]);

    return $slip;
}

public function generate(Request $request)
{
    $karyawanId = $request->input('karyawan');
    $kategori = $request->input('kategori');
    $periode = $request->input('periode');

    $karyawan = Karyawan::findOrFail($karyawanId);

    // Tangani jika kategori kosong atau "semua"
    if (!$kategori || $kategori === 'semua') {
        $periodeBulanan = now()->format('Y-m');
        $periodeMingguan = now()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $this->generateSlip($karyawan, $periodeBulanan, 'bulanan');
        $this->generateSlip($karyawan, $periodeMingguan, 'mingguan');

        return back()->with('success', "Slip gaji untuk {$karyawan->nama} berhasil digenerate untuk kategori *bulanan dan mingguan*.");
    }

    // Format periode sesuai kategori
    if ($kategori === 'bulanan') {
        $periode = Carbon::parse($periode)->format('Y-m');
    } elseif ($kategori === 'mingguan') {
        $periode = Carbon::parse($periode)->format('Y-m-d');
    }

    $this->generateSlip($karyawan, $periode, $kategori);

    return back()->with('success', "Slip gaji untuk {$karyawan->nama} periode {$periode} berhasil digenerate.");
}

  protected function getRangeFromPeriode(string $periode, string $kategori)
{
    if (strtolower($kategori) === 'mingguan') {
        $carbon = Carbon::parse($periode)->startOfWeek(Carbon::MONDAY);
        $start = $carbon;
        $end = $carbon->copy()->endOfWeek(Carbon::SUNDAY);
        return [$start->startOfDay(), $end->endOfDay()];
    } else {
        // Coba parsing dari 'Y-m' dulu
        try {
            $start = Carbon::createFromFormat('Y-m', $periode)->startOfMonth();
            $end = Carbon::createFromFormat('Y-m', $periode)->endOfMonth();
        } catch (\Exception $e) {
            // Kalau gagal, coba parsing dari tanggal penuh
            try {
                $start = Carbon::parse($periode)->startOfMonth();
                $end = Carbon::parse($periode)->endOfMonth();
            } catch (\Exception $ex) {
                throw new \Exception("Format periode bulanan tidak valid. Gunakan format '2025-06' atau tanggal di bulan tersebut.");
            }
        }
        return [$start->startOfDay(), $end->endOfDay()];
    }
}

    // ** Tambahan: Preview PDF slip gaji **
    public function previewPdf(SlipGaji $slipGaji)
    {
        if (!$slipGaji->file_pdf || !Storage::disk('public')->exists(str_replace('storage/', '', $slipGaji->file_pdf))) {
            abort(404, 'File PDF tidak ditemukan');
        }

        $fileRelativePath = str_replace('storage/', '', $slipGaji->file_pdf);
        $filePath = storage_path('app/public/' . $fileRelativePath);

        return response()->file($filePath);
    }

  public function downloadPdf(SlipGaji $slipGaji)
{
    $relativePath = str_replace('storage/', '', $slipGaji->file_pdf);
    $fullPath = storage_path('app/public/' . $relativePath);


    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan');
    }

    return response()->file($fullPath, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="SlipGaji_' . $slipGaji->karyawan->nama . '_' . $slipGaji->periode . '.pdf"',
    ]);
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
            $this->generateSlip($karyawan, $periode, strtolower($karyawan->gajiKaryawan->kategori_gaji));
;
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

        $slip = $this->generateSlip($karyawan, $periode, strtolower($karyawan->gajiKaryawan->kategori_gaji));
;

        return redirect()->route('slip_gaji.index')->with('success', "Slip gaji sudah dihitung untuk {$karyawan->nama} periode {$periode}");
    }
    public function kirimWhatsapp(SlipGaji $slipGaji)
    {
        $karyawan = $slipGaji->karyawan;
        $nomorRaw = $karyawan->nomor_telepon; // Nomor di DB: "0812 3456 7890"

        // Fungsi bersihkan dan format nomor WA
        $nomor = preg_replace('/[^0-9]/', '', $nomorRaw); // Hapus spasi dan karakter bukan digit
        
        // Ganti awalan 0 dengan 62
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }

        $pesan = "Halo {$karyawan->nama}, berikut slip gaji Anda untuk periode {$slipGaji->periode}. Total dibayar: Rp" . number_format($slipGaji->total_dibayar, 0, ',', '.');

        $urlPdf = asset($slipGaji->file_pdf);

        // Buat URL WhatsApp
        $linkWA = "https://wa.me/{$nomor}?text=" . urlencode($pesan . "\n\nDownload Slip Gaji: $urlPdf");

        return redirect($linkWA);
    }
   public function generateMassal(Request $request)
{
    $request->validate([
        'selected' => 'required|array',
        'periode' => 'required',
        'kategori' => 'nullable|string',
    ]);

    $kategori = strtolower($request->kategori ?? ''); // bisa null
    $periode = $request->periode;
    $now = Carbon::now();
    $generated = [];

    foreach ($request->selected as $id) {
        $karyawan = Karyawan::find($id);
        if (!$karyawan || !$karyawan->gajiKaryawan) continue;

        $gaji = $karyawan->gajiKaryawan;
        $kategoriGaji = strtolower($gaji->kategori_gaji);

        // Jika kategori "semua" dan periode adalah array (range)
        if ($kategori === 'semua' && is_array($periode)) {
            $start = Carbon::parse($periode[0]);
            $end = Carbon::parse($periode[1]);

            if ($end->greaterThan($now)) {
                $end = $now;
            }

            $periodeList = collect();
            while ($start <= $end) {
                if ($kategoriGaji === 'bulanan') {
                    $periodeList->push($start->format('Y-m'));
                    $start->addMonth();
                } elseif ($kategoriGaji === 'mingguan') {
                    $periodeList->push($start->format('Y-m-d'));
                    $start->addWeek();
                }
            }

            foreach ($periodeList as $p) {
                $this->generateSlip($karyawan, $p, $kategoriGaji);
            }
        } else {
            // Jika bukan kategori "semua"
            $finalPeriode = is_array($periode) ? $periode[0] : $periode;
            $this->generateSlip($karyawan, $finalPeriode, $kategori ?: $kategoriGaji);
        }

        $generated[] = $karyawan->nama;
    }

    return back()->with('success', 'Slip gaji berhasil digenerate untuk: ' . implode(', ', $generated));
}

    public function downloadMassal(Request $request)
    {
        $request->validate([
            'slip_ids' => 'required|array',
        ]);

        $zipFileName = 'slip_gaji_massal_' . now()->timestamp . '.zip';
        $zipPath = storage_path("app/public/{$zipFileName}");

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            foreach ($request->slip_ids as $id) {
                $slip = SlipGaji::find($id);
                if ($slip && $slip->file_pdf) {
                    $fileRelativePath = str_replace('storage/', '', $slip->file_pdf);
                    $filePath = storage_path('app/public/' . $fileRelativePath);

                    if (file_exists($filePath)) {
                        $namaFile = "Slip_{$slip->karyawan->nama}_{$slip->periode}.pdf";
                        $zip->addFile($filePath, $namaFile);
                    }
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
    public function kirimMassal(Request $request)
{
    $request->validate([
        'slip_ids' => 'required|array',
    ]);

    $links = [];

    foreach ($request->slip_ids as $id) {
        $slip = SlipGaji::find($id);
        if (!$slip) continue;

        $karyawan = $slip->karyawan;
        $nomor = preg_replace('/[^0-9]/', '', $karyawan->nomor_telepon);
        if (substr($nomor, 0, 1) === '0') {
            $nomor = '62' . substr($nomor, 1);
        }

        $pesan = "Halo {$karyawan->nama}, berikut slip gaji Anda untuk periode {$slip->periode}. Total dibayar: Rp" . number_format($slip->total_dibayar, 0, ',', '.');
        $urlPdf = asset($slip->file_pdf);
        $linkWA = "https://wa.me/{$nomor}?text=" . urlencode($pesan . "\n\nDownload Slip Gaji: $urlPdf");

        $links[] = [
            'nama' => $karyawan->nama,
            'link' => $linkWA
        ];
    }

    return view('slip_gaji.link_whatsapp_massal', compact('links'));
}
public function generateSlipRequest(Request $request)
{
    $karyawan = Karyawan::findOrFail($request->karyawan);
    $gaji = $karyawan->gajiKaryawan;

    if (!$gaji) {
        return back()->with('error', "Gaji untuk {$karyawan->nama} belum diatur.");
    }

    $kategori = strtolower($request->kategori ?? $gaji->kategori_gaji);
    $periode = $request->periode;

    // Jika kategori 'semua' dan periode adalah array (range)
    if ($kategori === 'semua' && is_array($periode)) {
        $start = Carbon::parse($periode[0]);
        $end = Carbon::parse($periode[1]);
        $now = Carbon::now();

        // Batasi tidak boleh lebih dari hari ini
        if ($end->greaterThan($now)) {
            $end = $now;
        }

        $periodeList = collect();
        while ($start <= $end) {
            if ($gaji->kategori_gaji === 'bulanan') {
                $periodeList->push($start->format('Y-m'));
                $start->addMonth();
            } elseif ($gaji->kategori_gaji === 'mingguan') {
                $periodeList->push($start->format('Y-m-d'));
                $start->addWeek();
            }
        }

        foreach ($periodeList as $p) {
            $this->generateSlip($karyawan, $p, $gaji->kategori_gaji);
        }

        return back()->with('success', "Slip gaji untuk {$karyawan->nama} berhasil digenerate dari beberapa periode.");
    }

    // Jika bukan array atau kategori bulanan/mingguan
    $finalPeriode = is_array($periode) ? $periode[0] : $periode;

    $this->generateSlip($karyawan, $finalPeriode, $kategori);

    return back()->with('success', "Slip gaji untuk {$karyawan->nama} periode {$finalPeriode} berhasil digenerate.");
}  


}
