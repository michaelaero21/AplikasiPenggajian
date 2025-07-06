<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\Karyawan;
use App\Models\SlipGaji;
use App\Models\GajiKaryawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;
use ZipArchive;
use App\Models\ThrFlag;
use Carbon\Carbon;

class SlipGajiController extends Controller
{
public function index(Request $request)
{
    /* -------- 1. Ambil filter dari query‑string -------- */
    $kategoriFilterRaw = $request->input('kategori');   // '' | mingguan | bulanan | semua
    $kategoriFilter = $kategoriFilterRaw && $kategoriFilterRaw !== 'semua'
                    ? $kategoriFilterRaw
                    : null;                             // null = tampilkan semua

    $startDate = $request->input('start_date');         // YYYY‑MM‑DD | null
    $endDate   = $request->input('end_date');           // YYYY‑MM‑DD | null
    $periode   = $request->input('periode');            // YYYY‑MM‑DD | YYYY‑MM | null

    /* -------- 2. Validasi range tanggal -------- */
    if ($startDate && $endDate) {
        try {
            $s = Carbon::parse($startDate);
            $e = Carbon::parse($endDate);
            if ($s->gt($e)) {
                return back()->withInput()
                             ->with('error', 'Tanggal awal lebih besar dari tanggal akhir.');
            }
        } catch (\Exception $ex) {
            return back()->withInput()
                         ->with('error', 'Format tanggal tidak valid.');
        }
    }

    /* -------- 3. Fallback periode bulanan -------- */
    if (!$periode && $startDate && $kategoriFilter === 'bulanan') {
        $periode = Carbon::parse($startDate)->format('Y-m');
    }

    /* -------- 4. Hitung rangeTanggal (opsional) -------- */
    $rangeTanggal = null;
    if ($periode && $kategoriFilter) {
        try {
            $rangeTanggal = $this->getRangeFromPeriode($periode, $kategoriFilter);
        } catch (\Exception $e) {
            $rangeTanggal = null;
        }
    }

    /* -------- 5. Data karyawan (tak berubah) -------- */
    $karyawans = Karyawan::query()
        ->when($kategoriFilter, function ($q) use ($kategoriFilter) {
            $q->whereHas('gajiKaryawan',
                fn ($sub) => $sub->where('kategori_gaji', $kategoriFilter));
        })
        ->with(['gajiKaryawan', 'slipGaji'])
        ->get();

    $kategoriGajiList = GajiKaryawan::pluck('kategori_gaji')->unique();

    /* ==========================================================
     * 6.  ===== NEW:  Query slip yang SUDAH ADA sesuai filter ===
     * ========================================================== */
    $slipQuery = SlipGaji::with('karyawan')
        ->when($kategoriFilter, fn ($q) =>
            $q->where('kategori_gaji', $kategoriFilter));

    // a) Filter periode spesifik jika ada
    if ($periode) {
        $slipQuery->whereIn('periode', (array) $periode);
    }

    // b) Filter range tanggal jika user set start_date / end_date
    if ($startDate || $endDate) {
        $from = $startDate ?: '0000-01-01';
        $to   = $endDate   ?: '9999-12-31';

        $slipQuery->where(function ($q) use ($from, $to) {
            // mingguan (periode = YYYY‑MM‑DD)
            $q->where(function ($s) use ($from, $to) {
                    $s->whereRaw('LENGTH(periode)=10')
                      ->whereBetween('periode', [$from, $to]);
                })
                // bulanan (periode = YYYY‑MM) – treat as first day
                ->orWhere(function ($s) use ($from, $to) {
                    $s->whereRaw('LENGTH(periode)=7')
                      ->whereBetween(\DB::raw("CONCAT(periode,'-01')"), [$from, $to]);
                });
        });
    }

    // Ambil slip yang memang sudah dibuat (bisa → get() / paginate())
    $slips = $slipQuery->latest('periode')->get();   // ===== NEW =====

    /* -------- 7. Kirim ke view -------- */
    return view('slip_gaji.index', compact(
        'karyawans',
        'kategoriGajiList',
        'kategoriFilterRaw',
        'kategoriFilter',
        'periode',
        'startDate',
        'endDate',
        'rangeTanggal',
        'slips'              // ===== NEW =====
    ));
}

public function generateSlipFromIndex(Request $request)
{
    $id       = $request->input('karyawan');
    $periode  = $request->input('periode');
    $kategori = strtolower($request->input('kategori'));

    // validasi singkat …
    if (!$id || !$periode) {
        return back()->with('error', 'Karyawan dan periode harus diisi.');
    }
    if ($kategori === 'semua' || !$kategori) {
        return back()->with('warning', 'Kategori "semua" hanya untuk tampilan.');
    }

    $karyawan = Karyawan::find($id);
    if (!$karyawan) return back()->with('error', 'Karyawan tidak ditemukan.');

    /* ---------- proses generate ---------- */
    if ($kategori === 'bulanan') {
        $periode = Carbon::parse($periode)->format('Y-m');
        $this->generateSlipBulanan($karyawan, $periode, 'bulanan');
   } elseif ($kategori === 'mingguan') {

    /* ----------- 1. Ambil tanggal yang dipilih user ----------- */
    // form Anda mungkin mengirim `periode`, atau `start_date`,
    // jadi ambil mana pun yang ada
    $input = $request->input('start_date') ?: $periode;
    $dipilih = Carbon::parse($input)->startOfDay();

    /* ----------- 2. Tolak kalau Minggu ----------- */
    if ($dipilih->isSunday()) {
        return back()->with('error',
            'Tanggal Minggu tidak diperbolehkan untuk slip mingguan.');
    }

    /* ----------- 3. Hitung Senin dan Sabtu di minggu yang sama ----------- */
    $mulai = $dipilih->copy()->startOfWeek(Carbon::MONDAY); // selalu Senin (00:00)
    $akhir = $mulai->copy()->addDays(5)->endOfDay();        // Sabtu (23:59)

    /* ----------- 4. Simpan nilai yang sudah bersih ----------- */
    // kolom `periode` pakai tanggal Senin
    $periode = $mulai->format('Y-m-d');

    // kalau Anda memang menyimpan kolom start_date / end_date,
    // gabungkan ke $request supaya fungsi generateSlipMingguan tidak perlu menebak
    $request->merge([
        'start_date' => $mulai->toDateString(),
        'end_date'   => $akhir->toDateString(),
    ]);

    /* ----------- 5. Generate ----------- */
    $this->generateSlipMingguan($karyawan, $periode);


    } else {
        return back()->with('error', 'Kategori tidak dikenali.');
    }

    /* ---------- redirect dgn seluruh filter ---------- */
    $params = $request->only(['start_date', 'end_date']); // range bila ada
    $params['periode']  = $periode;   // periode yg sudah dinormalkan
    $params['kategori'] = $kategori;

    return redirect()
           ->route('slip-gaji.index', $params)
           ->with('success', "Slip gaji {$karyawan->nama} periode {$periode} berhasil digenerate.");
}

   public function generateSlip(Karyawan $karyawan, string $periode, string $kategori = null)
{
    $gaji = $karyawan->gajiKaryawan;
    if (!$gaji) {
        throw new \Exception("Data gaji belum diatur untuk karyawan: {$karyawan->nama}");
    }

    $kategori = strtolower($kategori ?: $gaji->kategori_gaji);
    $range = $this->getRangeFromPeriode($periode, $kategori);
    $adaAbsensi = Absensi::where('karyawan_id', $karyawan->id)
        ->whereBetween('tanggal', $range)   // seluruh status, tidak hanya “hadir”
        ->exists();

    if (!$adaAbsensi) {
        throw new \Exception(
            "Tidak bisa membuat slip—belum ada data absensi untuk "
            . "{$karyawan->nama} pada periode {$periode} ({$kategori})."
        );
    }
    $isBulanan = $kategori === 'bulanan';
    $isMingguan = $kategori === 'mingguan';

    // Hitung jumlah kehadiran
    $absensiQuery = Absensi::where('karyawan_id', $karyawan->id)
        ->whereBetween('tanggal', $range)
        ->whereIn('status', ['hadir', 'H', 'HADIR']);

    // Jika mingguan, filter Senin–Sabtu saja
    // if ($isMingguan) {
    //     $absensiQuery->whereRaw('DAYOFWEEK(tanggal) BETWEEN 2 AND 7');
    // }

    $jumlah_hadir = $absensiQuery->count();

    // Komponen gaji (disesuaikan dengan kategori)
    $uang_makan_per_hari = $gaji->uang_makan ?? 0;
    $uang_lembur_per_jam = $gaji->uang_lembur ?? 0;

    $uang_transport   = $isBulanan ? ($gaji->uang_transportasi ?? 0) : 0;
    $tunjangan_pulsa  = $isBulanan ? ($gaji->tunjangan_pulsa ?? 0) : 0;
    $gaji_pokok       = $isBulanan ? ($gaji->gaji_pokok ?? 0) : 0;
    $thr = $this->hasThrFlag($karyawan->id, $periode, $kategori)
        ? ($gaji->thr ?? 0)
        : 0;
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
        $data['asuransi'],
    ]);

    $slip = SlipGaji::updateOrCreate(
        [
            'karyawan_id' => $karyawan->id,
            'periode' => $periode,
            'kategori_gaji' => $kategori,
        ],
        $data
    );
    $periodeTeks = $this->formatPeriode($slip);
    $pdf = PDF::loadView('slip_gaji.pdf', [
        'slip' => $slip,
        'karyawan' => $karyawan,
        'periode' => $periodeTeks,
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
    $kategori   = strtolower($request->input('kategori'));
    $periode    = $request->input('periode');

    $karyawan = Karyawan::findOrFail($karyawanId);

    if ($kategori === 'semua' || !$kategori) {
        return back()->with('warning', 'Kategori "semua" hanya untuk tampilan.');
    }

    if ($kategori === 'bulanan') {
        $periode = Carbon::parse($periode)->format('Y-m');
        $this->generateSlipBulanan($karyawan, $periode);
    } elseif ($kategori === 'mingguan') {
       if (Carbon::parse($periode)->dayOfWeek === Carbon::SUNDAY) {
                return back()->with(
                    'error',
                    'Tanggal periode mingguan tidak boleh hari Minggu. Silakan pilih tanggal Senin – Sabtu.'
                );
            }

            // **tidak ada lagi** startOfWeek()
            $periode = Carbon::parse($periode)->format('Y-m-d');
            $this->generateSlipMingguan($karyawan, $periode);
    } else {
        return back()->with('error', 'Kategori tidak dikenali.');
    }

    /* ---------- redirect dgn filter tetap ---------- */
    $params = $request->only(['start_date', 'end_date']);
    $params['periode']  = $periode;
    $params['kategori'] = $kategori;

    return redirect()
           ->route('slip-gaji.index', $params)
           ->with('success', "Slip gaji {$karyawan->nama} periode {$periode} ({$kategori}) berhasil digenerate.");
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
/** Apakah $startOfWeek berada di minggu terakhir bulan tsb? */
protected function isLastWeekOfMonth(Carbon $startOfWeek): bool
{
    return $startOfWeek->copy()->addWeek()->month !== $startOfWeek->month;
}

/** 1) Wrapper slip bulanan – logika lama tetap dipakai */
protected function generateSlipBulanan(Karyawan $karyawan, string $periode): SlipGaji
{
    return $this->generateSlip($karyawan, $periode, 'bulanan');   // panggil mesin lama
}

/** 2) Slip mingguan – uang makan tiap minggu,
 *    lembur + komponen bulanan hanya minggu terakhir
 */
protected function generateSlipMingguan(Karyawan $karyawan, string $periode): SlipGaji
{
    if (Carbon::parse($periode)->dayOfWeek === Carbon::SUNDAY) {   // 0 = Sunday
        throw new \Exception(
            'Tanggal periode mingguan tidak boleh hari Minggu. '
          . 'Silakan pilih tanggal Senin–Sabtu.'
        );
    }
    $gaji = $karyawan->gajiKaryawan;
    if (!$gaji) throw new \Exception("Data gaji {$karyawan->nama} belum diatur.");

    /* ---------- range & status minggu ---------- */
    [$start, $end] = $this->getRangeFromPeriode($periode, 'mingguan');
    $isLastWeek    = $this->isLastWeekOfMonth(Carbon::parse($start));

    /* ---------- hadir Senin-Sabtu ---------- */
    $hadir = Absensi::where('karyawan_id',$karyawan->id)
                    ->whereBetween('tanggal',[$start,$end])
                    ->whereRaw('DAYOFWEEK(tanggal) BETWEEN 2 AND 7')
                    ->whereIn('status',['hadir','H','HADIR'])
                    ->count();

    $uangMakan = $hadir * ($gaji->uang_makan ?? 0);

    /* ---------- lembur: total 1 bulan, dibayar minggu terakhir ---------- */
    $lembur = 0;
    if ($isLastWeek) {
        $monthStart = Carbon::parse($start)->startOfMonth();
        $absensiLembur = Absensi::where('karyawan_id',$karyawan->id)
                                ->whereBetween('tanggal',[$monthStart,$end])
                                ->whereNotNull('jam_pulang')
                                ->get();

        $jam = 0;
        foreach ($absensiLembur as $a) {
            $pulang = Carbon::parse($a->tanggal.' '.$a->jam_pulang);
            $batas  = Carbon::parse($a->tanggal)->setTime(18,0,0);
            if ($pulang->gt($batas)) {
                $jam += max(1, ceil($batas->diffInMinutes($pulang)/60));
            }
        }
        $lembur = $jam * ($gaji->uang_lembur ?? 0);
    }

    /* ---------- susun data ---------- */
    $data = [
        'karyawan_id'    => $karyawan->id,
        'gaji_id'        => $gaji->id,
        'periode'        => $periode,
        'kategori_gaji'  => 'mingguan',
        'jumlah_hadir'   => $hadir,
        'uang_makan'     => $uangMakan,
        'lembur'         => $lembur,
        // komponen bulanan hanya minggu terakhir
        'gaji_pokok'     => $isLastWeek ? ($gaji->gaji_pokok ?? 0)        : 0,
        'uang_transport' => $isLastWeek ? ($gaji->uang_transportasi ?? 0) : 0,
        'tunjangan_pulsa'=> $isLastWeek ? ($gaji->tunjangan_pulsa ?? 0)   : 0,
        'tunjangan_sewa' => $isLastWeek ? ($gaji->tunjangan_sewa_transport ?? 0):0,
        'thr' => $this->hasThrFlag($karyawan->id, $periode, 'mingguan')
            ? ($gaji->thr ?? 0)
            : 0,
        'insentif'       => $isLastWeek ? ($gaji->insentif ?? 0)          : 0,
        'asuransi'       => $isLastWeek ? ($gaji->asuransi ?? 0)          : 0,
        'bonus'          => 0,
        'start_date' => $start->toDateString(),   // ← tambah
        'end_date'   => $end->toDateString(),     // ← tambah
    ];

    $data['total_dibayar'] = array_sum([
        $data['gaji_pokok'],$data['uang_makan'],$data['uang_transport'],
        $data['tunjangan_pulsa'],$data['tunjangan_sewa'],$data['lembur'],
        $data['bonus'],$data['thr'],$data['insentif'],
        $data['asuransi'],
    ]);

    $slip = SlipGaji::updateOrCreate(
        ['karyawan_id'=>$karyawan->id,'periode'=>$periode,'kategori_gaji'=>'mingguan'],
        $data
    );

    $periodeTeks = $this->formatPeriode($slip);
    $pdf = PDF::loadView('slip_gaji.pdf', [
        'slip' => $slip,
        'karyawan' => $karyawan,
        'periode' => $periodeTeks,
        'gaji' => $gaji,
    ]);
    $file = 'slips/slip_'.Str::slug($karyawan->nama)."_{$periode}_mingguan.pdf";
    Storage::disk('public')->put($file,$pdf->output());
    $slip->update(['file_pdf'=>'storage/'.$file]);

    return $slip;
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
    // 1 ▸ Validasi file PDF
    if (!$slipGaji->file_pdf) {
        abort(404, 'File PDF tidak ditemukan.');
    }

    $relativePath = str_replace('storage/', '', $slipGaji->file_pdf);
    $filePath     = storage_path('app/public/' . $relativePath);

    if (!is_file($filePath)) {
        abort(404, 'File PDF tidak tersedia di penyimpanan.');
    }

    // 2 ▸ Tampilkan inline (biar bisa preview di browser)
    return response()->file($filePath, [
        'Content-Type' => 'application/pdf',
        // Tidak pakai Content-Disposition: attachment
        // Browser akan otomatis preview
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
    /* ─── 0.  Terima ‘selected’ baik CSV maupun array ─── */
    $ids = $request->input('selected');
    if (is_string($ids)) {
        $ids = array_filter(explode(',', $ids));
        $request->merge(['selected' => $ids]);
    }

    /* ─── 1. Validasi ─── */
    $request->validate([
        'selected'    => 'required|array|min:1',
        'selected.*'  => 'integer|exists:karyawans,id',
        'periode'     => 'required',            // string YYYY‑MM / YYYY‑MM‑DD   atau array [start,end]
        'periode.*'   => 'date',
        'kategori'    => 'nullable|in:mingguan,bulanan,semua',
    ]);

    /* ─── 2. Normalisasi input ─── */
    $kategoriInput = strtolower($request->kategori ?? ''); // '' ‑> pakai kategori karyawan
    if ($kategoriInput === 'semua') $kategoriInput = '';   // perlakuan sama

    $periodeInput  = $request->periode;                    // string / array
    $now           = now();
    $generated     = [];

    /* ─── 3. Loop karyawan ─── */
    foreach ($request->selected as $kid) {
        $karyawan      = Karyawan::with('gajiKaryawan')->find($kid);
        if (!$karyawan || !$karyawan->gajiKaryawan) continue;

        // kategori final utk karyawan ini
        $kategoriFinal = $kategoriInput ?: strtolower($karyawan->gajiKaryawan->kategori_gaji);

        /* === A. Hitung daftar periode === */
        $periodeList = collect();

        // 3A‑1. Jika input berupa rentang [start,end]
        if (is_array($periodeInput) && count($periodeInput) === 2) {
            $start = Carbon::parse($periodeInput[0])->startOfDay();
            $end   = Carbon::parse($periodeInput[1])->endOfDay();
            if ($end->greaterThan($now)) $end = $now;

            while ($start <= $end) {
                if ($kategoriFinal === 'bulanan') {
                    $periodeList->push($start->format('Y-m'));   // 2025‑07
                    $start->addMonth();
                } else { // mingguan
                    $periodeList->push($start->format('Y-m-d')); // 2025‑07‑07 (Senin)
                    $start->addWeek();
                }
            }

        // 3A‑2. Satu string periode saja
        } else {
            $periodeList->push(is_array($periodeInput) ? $periodeInput[0] : $periodeInput);
        }

        /* === B. Generate slip untuk setiap periode === */
        foreach ($periodeList as $p) {
            $this->generateSlip($karyawan, $p, $kategoriFinal);
        }

        $generated[] = $karyawan->nama;
    }

    /* ─── 4. Flash hasil ─── */
    return back()->with(
        'success',
        'Slip gaji berhasil digenerate untuk: ' . implode(', ', $generated)
    );
}

   /**
 * Gabung PDF lalu tampilkan ZIP secara inline (browser akan otomatis
 * men‑download karena MIME‑type application/zip tidak bisa di‑render).
 */
public function downloadMassal(Request $request)
{
    /* 1 ▸ slip_ids → array */
    $ids = $request->input('slip_ids');
    if (is_string($ids)) {
        $ids = array_filter(explode(',', $ids));
        $request->merge(['slip_ids' => $ids]);
    }

    /* 2 ▸ validasi */
    $request->validate([
        'slip_ids'   => 'required|array|min:1',
        'slip_ids.*' => 'integer',
    ]);

    /* 3 ▸ buat ZIP */
    $zipName = 'slip_gaji_massal_' . now()->timestamp . '.zip';
    $zipPath = storage_path("app/public/temp_zip/$zipName");

    if (!is_dir(dirname($zipPath))) {
        mkdir(dirname($zipPath), 0755, true);
    }

    $zip   = new \ZipArchive;
    $added = 0;

    if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
        foreach ($request->slip_ids as $id) {
            $slip = SlipGaji::find($id);
            if (!$slip || !$slip->file_pdf) continue;

            $fileRel = str_replace('storage/', '', $slip->file_pdf);
            $fileAbs = storage_path('app/public/' . $fileRel);

            if (is_file($fileAbs)) {
                $fileName = "Slip_{$slip->karyawan->nama}_{$slip->periode}.pdf";
                $zip->addFile($fileAbs, $fileName);
                $added++;
            }
        }
        $zip->close();
    }

    if ($added === 0) {
        return back()->with('error', 'Tidak ada file slip gaji valid untuk diarsipkan.');
    }

    /* 4 ▸ tampilkan ZIP “inline” */
    return response()->file($zipPath, [
        'Content-Type' => 'application/zip',
        // tidak pakai Content‑Disposition → browser memutuskan sendiri
    ]);
}


    public function kirimMassal(Request $request)
{
    /* 1 ▸ ubah string CSV → array */
    $ids = $request->input('slip_ids');
    if (is_string($ids)) {
        $ids = array_filter(explode(',', $ids));
        $request->merge(['slip_ids' => $ids]);
    }

    /* 2 ▸ validasi */
    $request->validate([
        'slip_ids'   => 'required|array|min:1',
        'slip_ids.*' => 'integer',
    ]);

    /* 3 ▸ susun tautan */
    $links = [];
    foreach ($request->slip_ids as $id) {
        $slip = SlipGaji::find($id);
        if (!$slip || !$slip->file_pdf) continue;

        $karyawan = $slip->karyawan;
        $nomor    = preg_replace('/\D/', '', $karyawan->nomor_telepon);
        if (str_starts_with($nomor, '0')) $nomor = '62' . substr($nomor, 1);

        $pesan  = "Halo {$karyawan->nama}, berikut slip gaji Anda ".
                  "untuk periode {$slip->periode}. Total dibayar: Rp".
                  number_format($slip->total_dibayar, 0, ',', '.');
        $urlPdf = asset($slip->file_pdf);
        $links[] = [
            'nama' => $karyawan->nama,
            'link' => "https://wa.me/{$nomor}?text=".
                      urlencode($pesan."\n\nDownload Slip Gaji: $urlPdf")
        ];
    }

    /* 4 ▸ jika tak ada link valid */
    if (empty($links)) {
        return back()->with('error', 'Tidak ada slip gaji valid untuk dikirim.');
    }

    /* 5 ▸ tampilkan daftar link */
    return view('slip_gaji.link_whatsapp_massal', compact('links'));
}
/**
 * Tandai THR secara massal.
 * Dipanggil dari toolbar centang‐massal (bulk‑thr).
 * Form harus mengirim:
 *   - selected[]   → id karyawan
 *   - periode      → YYYY‑MM  | YYYY‑MM‑DD
 *   - kategori     → 'bulanan' | 'mingguan'
 */
public function setThrFlagMassal(Request $request)
{
    /* --- 1. pastikan selected selalu array --- */
    $ids = $request->input('selected');
    if (is_string($ids)) {
        $ids = array_filter(explode(',', $ids));
        $request->merge(['selected' => $ids]);
    }

    /* --- 2. validasi --- */
    $request->validate([
        'selected'   => 'required|array|min:1',
        'selected.*' => 'integer',
        'periode'    => 'required|string',
        'kategori'   => 'required|in:bulanan,mingguan',
    ]);

    $periode  = $request->periode;
    $kategori = strtolower($request->kategori);

    $berhasil = [];
    foreach ($request->selected as $kid) {
        $karyawan = Karyawan::find($kid);
        if (!$karyawan) continue;

        ThrFlag::updateOrCreate(
            [
                'karyawan_id' => $karyawan->id,
                'periode'     => $periode,
                'kategori'    => $kategori,
            ],
            []   // tidak ada kolom tambahan
        );
        $berhasil[] = $karyawan->nama;
    }

    if (empty($berhasil)) {
        return back()->with('error', 'THR gagal ditambahkan – tidak ada karyawan valid.');
    }

    return back()->with(
        'success',
        'THR berhasil ditandai untuk: '.implode(', ', $berhasil)
    );
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
public function indexKaryawan(Request $request)
{
    $user     = Auth::user();
    $karyawan = $user->karyawan;                    // relasi hasOne

    if (!$karyawan) {
        return view('slip_gaji.karyawan')
               ->with('message', 'Data karyawan Anda belum dibuat.');
    }

    /* -------------------------------------------------
     * 1.  Ambil parameter filter
     * ------------------------------------------------- */
    $start = $request->query('start');   // format YYYY-MM-DD
    $end   = $request->query('end');     // format YYYY-MM-DD

    /* ---------- validasi range ---------- */
    if ($start && $end && $start > $end) {
        return back()
            ->withInput()
            ->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
    }

    /* -------------------------------------------------
     * 2.  Bangun query slip gaji
     * ------------------------------------------------- */
    $query = SlipGaji::where('karyawan_id', $karyawan->id);

    if ($start || $end) {
        // kalau salah satu kosong, pakai nilai ekstrem agar tetap kena between
        $startDate = $start ?: '0000-01-01';
        $endDate   = $end   ?: '9999-12-31';

        $query->where(function ($q) use ($startDate, $endDate) {
            // a) slip mingguan (periode = YYYY-MM-DD)
            $q->where(function ($q2) use ($startDate, $endDate) {
                $q2->whereRaw('LENGTH(periode) = 10')
                   ->whereBetween('periode', [$startDate, $endDate]);
            })
            // b) slip bulanan  (periode = YYYY-MM  → CONCAT dengan -01)
            ->orWhere(function ($q2) use ($startDate, $endDate) {
                $q2->whereRaw('LENGTH(periode) = 7')
                   ->whereBetween(\DB::raw("CONCAT(periode,'-01')"), [$startDate, $endDate]);
            });
        });
    }

    $slips = $query->latest('periode')
                   ->paginate(12)
                   ->withQueryString();

    /* -------------------------------------------------
     * 3.  Slip terakhir & total gaji diterima
     * ------------------------------------------------- */
    $slipTerakhir = SlipGaji::where('karyawan_id', $karyawan->id)
                            ->latest('updated_at')
                            ->first();
    $gajiDiterima = $slipTerakhir->total_dibayar ?? 0;

    /* -------------------------------------------------
     * 4.  Format periode setiap slip
     * ------------------------------------------------- */
    $slips->getCollection()->transform(function (SlipGaji $s) {
        $s->periode_formatted = $this->formatPeriode($s);
        return $s;
    });

    /* -------------------------------------------------
     * 5.  Hitung gajian berikutnya
     * ------------------------------------------------- */
    $today   = Carbon::now('Asia/Jakarta')->startOfDay();
    $nextPay = Carbon::create($today->year, $today->month, 1)->startOfDay();
    if ($today->gt($nextPay)) $nextPay->addMonth();

    /* -------------------------------------------------
     * 6.  Tampilkan view
     * ------------------------------------------------- */
    return view('slip_gaji.karyawan', [
        'slips'            => $slips,
        'karyawan'         => $karyawan,
        'slipTerakhir'     => $slipTerakhir,
        'gajiDiterima'     => $gajiDiterima,
        'selisihHari'      => $today->diffInDays($nextPay, false),
        'tanggalGajian'    => $nextPay,
        'tanggalGajianIso' => $nextPay->toIso8601String(),
    ]);
}

/**
 * Download PDF – hanya kalau slip milik user
 */
public function downloadPdfKaryawan(SlipGaji $slipGaji)
{
    $karyawanIdUser = Auth::user()->karyawan->id ?? null;

    if ($slipGaji->karyawan_id !== $karyawanIdUser) {
        abort(403, 'Tidak diizinkan.');
    }

    $relative = str_replace('storage/', '', $slipGaji->file_pdf);
    $fullPath = storage_path("app/public/{$relative}");

    if (!file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan');
    }

    return response()->file($fullPath, [
        'Content-Type'        => 'application/pdf',
    ]);
}
public function previewPdfKaryawan(SlipGaji $slipGaji)
{
    // pastikan slip memang milik karyawan yg terkait user login
    $allowed = $slipGaji->karyawan           // relasi karyawan
              && $slipGaji->karyawan->user_id === Auth::id();

    if (! $allowed) {
        abort(403, 'Tidak diizinkan melihat slip ini.');
    }

    $relative = str_replace('storage/', '', $slipGaji->file_pdf);
    $fullPath = storage_path('app/public/' . $relative);

    if (! file_exists($fullPath)) {
        abort(404, 'File tidak ditemukan.');
    }

    return response()->file($fullPath);
}

public function setThrFlag(Request $request)
{
    $request->validate([
        'karyawan_id' => 'required|exists:karyawans,id',
        'periode'     => 'required|string',
        'kategori'    => 'required|in:bulanan,mingguan',
    ]);

    ThrFlag::updateOrCreate(
        [
            'karyawan_id' => $request->karyawan_id,
            'periode'     => $request->periode,
            'kategori'    => $request->kategori,
        ],
        []
    );
    $karyawan = Karyawan::find($request->karyawan_id);

    return back()->with(
        'success',
        "THR ditandai untuk {$karyawan->nama} periode {$request->periode}."
    );
}
/* Tempel di dalam SlipGajiController */
protected function hasThrFlag(int $karyawanId, string $periode, string $kategori): bool
{
    return ThrFlag::where([
        ['karyawan_id', $karyawanId],
        ['periode',     $periode],
        ['kategori',    $kategori],
    ])->exists();
}
  private function formatPeriode(SlipGaji $slip): string
    {
         Carbon::setLocale('id');

    if ($slip->kategori_gaji === 'bulanan') {
        return Carbon::parse("{$slip->periode}-01")->translatedFormat('F Y');
    }

    // --- mingguan ---
    // Jika start_date tersedia, pakai; jika tidak, turunkan dari 'periode'
    $start = $slip->start_date
        ? Carbon::parse($slip->start_date)
        : Carbon::parse($slip->periode)->startOfWeek(Carbon::MONDAY);

    $end   = $slip->end_date
        ? Carbon::parse($slip->end_date)
        : $start->copy()->addDays(5);

    return $start->translatedFormat('j F Y') . ' - ' . $end->translatedFormat('j F Y');
    }
    public function show($id)
    {
        // 1. Ambil data
        $slip     = SlipGaji::with('karyawan')->findOrFail($id);
        $karyawan = $slip->karyawan;

        // 2. Hitung teks periode
        $periode = $this->formatPeriode($slip);

        // 3. Kirim ke view
        return view('pdf', compact('slip', 'karyawan', 'periode'));
    }
protected function getKaryawanGroupedWithSlip($year, $month)
{
    // relasi slipGaji tetap di‑load agar bisa dipakai di view
    $all = Karyawan::with(['gajiKaryawan', 'slipGaji' => function ($q) use ($year, $month) {
        $q->whereYear('updated_at', $year)
          ->whereMonth('updated_at', $month);
    }])->get();

    return [
        'semua'            => $all,
        'mingguan'         => $all->where('gajiKaryawan.kategori_gaji', 'mingguan'),
        'bulanan'          => $all->where('gajiKaryawan.kategori_gaji', 'bulanan'),
        'belum_diketahui'  => $all->filter(fn ($k) => is_null(optional($k->gajiKaryawan)->kategori_gaji)),
    ];
}
public function showSlipAll(Request $request)
{
    $year  = (int) $request->get('year', now()->year);
    $month = (int) $request->get('month', now()->month);
    $tipe  = strtolower($request->get('tipe', 'semua'));   // “semua | mingguan | bulanan”

    // -------- data karyawan + slip ----------
    if ($tipe === 'semua') {
        $groups     = $this->getKaryawanGroupedWithSlip($year, $month);
        $karyawans  = $groups['semua'];        // untuk tabel default
    } else {
        // satu kategori saja → cukup pakai helper lama
        $karyawans  = $this->getKaryawanWithSlip($year, $month, $tipe); // tulis sendiri
        $groups     = null;                    // tak dipakai di view
    }

    // -------- daftar tanggal (bisa dipakai di table) ----------
    $days = $this->generateDays($year, $month);

    return view('slip_gaji.index', compact(
        'karyawans',
        'groups',         // <— baru
        'year',
        'month',
        'days',
        'tipe'
    ));
}

    
}