<?php
namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);
        $kategoriGaji = $request->input('kategori_gaji'); // bisa 'mingguan', 'bulanan', atau null
        $karyawanId = $request->input('karyawan_id');

        $karyawansData = $this->getKaryawanWithAbsensi($year, $month);
        $days = $this->generateDays($year, $month);

        // Karyawan berdasarkan kategori
        $karyawansMingguan = $karyawansData['Mingguan'];
        $karyawansBulanan = $karyawansData['Bulanan'];
        $karyawansAll = $karyawansMingguan->merge($karyawansBulanan)->sortBy('nama')->values();

        // Filter jika kategori_gaji dipilih
        if ($kategoriGaji === 'mingguan') {
            $karyawansBulanan = collect(); // kosongkan
        } elseif ($kategoriGaji === 'bulanan') {
            $karyawansMingguan = collect(); // kosongkan
        }

        // Filter karyawan spesifik jika ada
        if ($karyawanId) {
            $karyawansMingguan = $karyawansMingguan->where('id', $karyawanId);
            $karyawansBulanan = $karyawansBulanan->where('id', $karyawanId);
        }

        return view('absensi.index', [
            'karyawansMingguan' => $karyawansMingguan,
            'karyawansBulanan' => $karyawansBulanan,
            'karyawansAll' => $karyawansAll,
            'year' => $year,
            'month' => $month,
            'days' => $days,
        ]);
    }

     public function showAbsensi(Request $request)
{
    $year  = filter_var($request->get('year', now()->year), FILTER_VALIDATE_INT);
    $month = filter_var($request->get('month', now()->month), FILTER_VALIDATE_INT);
    $tipe  = strtolower($request->get('tipe', 'semua')); // lowercase untuk konsistensi

    if ($month < 1 || $month > 12) {
        $month = now()->month;
    }

    if ($tipe === 'semua') {
    $all = Karyawan::all();
    $karyawans = [
        'semua' => $all,
        'mingguan' => $all->where('kategori_gaji', 'Mingguan'),
        'bulanan' => $all->where('kategori_gaji', 'Bulanan'),
        'belum_diketahui' => $all->whereNull('kategori_gaji'),
    ];
    } else {
        $karyawans = $this->getKaryawanWithAbsensi($year, $month);
    }

    $days = $this->generateDays($year, $month);

    return view('absensi.index', compact('karyawans', 'year', 'month', 'days', 'tipe'));
}


    public function upload(Request $request)
{
    // Validasi file
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
    ]);

    $file = $request->file('file');
    $filename = time() . '_' . $file->getClientOriginalName();

    try {
        // Simpan file ke penyimpanan publik
        $path = $file->storeAs('uploads/absensi', $filename, 'public');
        Log::info('File berhasil disimpan di:', ['path' => $path]);

        // Baca file Excel menggunakan Maatwebsite Excel
        $excelData = Excel::toArray(new AbsensiImport, $file);

        // Ambil data sheet pertama untuk preview
        $dataPreview = $excelData[0]; // Misalnya mengambil data dari sheet pertama

        // Simpan data preview ke session untuk ditampilkan di view
        session(['preview_data' => $dataPreview, 'file_path' => $path]);

        // Arahkan ke halaman preview untuk melihat data sebelum import
        return redirect()->route('absensi.preview');
    } catch (\Exception $e) {
        // Log error jika gagal
        Log::error('Gagal menyimpan file: ' . $e->getMessage());
        return back()->with('error', 'Gagal menyimpan file: ' . $e->getMessage());
    }
}


    public function import(Request $request)
    {
        
        $request->validate([
            'filePath' => 'required|string',
        ]);

        $filePath = $request->input('filePath');

        if (!Storage::disk('public')->exists($filePath)) {
            return back()->with('error', 'File tidak ditemukan di storage.');
        }

        $fullPath = Storage::disk('public')->path($filePath);

        DB::beginTransaction();
        try {
            $spreadsheet = IOFactory::load($fullPath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $month = now()->month;
            if (!empty($rows[4][0]) && stripos($rows[4][0], 'Month:') !== false) {
                if (preg_match('/Month:\s*(\d{1,2})/', $rows[4][0], $m)) {
                    $month = (int) $m[1];
                }
            }

            $karyawanIdManual = $request->input('karyawan_id');
            $karyawan = null;

            if ($karyawanIdManual) {
                $karyawan = Karyawan::find($karyawanIdManual);
            } else {
                $namaKaryawan = trim((string) ($rows[6][0] ?? '')); 
                $namaKaryawan = str_ireplace('Nama:', '', $namaKaryawan);
                $namaKaryawan = trim($namaKaryawan);

                $userId = null;
                if (!empty($rows[6][1])) {
                    preg_match('/ID:\s*(\d+)/i', $rows[6][1], $id);
                    $userId = $id[1] ?? null;
                }

                if ($userId) {
                    $karyawan = Karyawan::find($userId);
                }
                if (!$karyawan && $namaKaryawan) {
                    $karyawan = Karyawan::where('nama', 'like', "%{$namaKaryawan}%")->first();
                }
            }

            if (!$karyawan) {
                throw new \Exception('Karyawan tidak ditemukan.');
            }

            $year = now()->year;

            $convertTime = function ($value) {
                if (is_numeric($value)) {
                    return Date::excelToDateTimeObject($value)->format('H:i:s');
                }
                return !empty($value) ? (string) $value : null;
            };

            $dates1 = $rows[7] ?? [];
            $in1 = $rows[8] ?? [];
            $out1 = $rows[9] ?? [];
            $dates2 = $rows[10] ?? [];
            $in2 = $rows[11] ?? [];
            $out2 = $rows[12] ?? [];

            foreach ([$dates1, $dates2] as $idx => $dates) {
                $ins = ${'in' . ($idx + 1)};
                $outs = ${'out' . ($idx + 1)};

                foreach ($dates as $col => $day) {
                    if (!is_numeric($day)) continue;

                    $day = (int) $day;
                    if ($day < 1 || $day > 31) continue;

                    $jamMasuk = $convertTime($ins[$col] ?? null);
                    $jamPulang = $convertTime($outs[$col] ?? null);
                    $tanggal = Carbon::create($year, $month, $day)->toDateString();

                    $status = ($jamMasuk && $jamPulang) ? 'H' : 'I';

                    Absensi::updateOrCreate(
                        [
                            'karyawan_id' => $karyawan->id,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'nama_karyawan' => $karyawan->nama,
                            'jam_masuk' => $jamMasuk,
                            'jam_pulang' => $jamPulang,
                            'status' => $status,
                        ]
                    );
                }
            }

            DB::commit();

           $kategoriGaji = strtolower($karyawan->kategori_gaji ?? '');

            if ($kategoriGaji === 'bulanan') {
                return redirect()->route('absensi.show', [
                    'tipe' => 'bulanan',
                    'month' => $month,
                    'year' => $year,
                ])->with('success', 'Import absensi '. $karyawan->nama .' bulanan berhasil.');
            } elseif ($kategoriGaji === 'mingguan') {
                return redirect()->route('absensi.show', [
                    'tipe' => 'mingguan',
                    'month' => $month,
                    'year' => $year,
                ])->with('success', 'Import absensi '. $karyawan->nama .' mingguan berhasil.');
            } else {
                return redirect()->route('absensi.show', [
                    'tipe' => 'semua',
                    'month' => $month,
                    'year' => $year,
                ])->with('success', 'Import absensi '. $karyawan->nama .' berhasil.');
            }  

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during import: ' . $e->getMessage());
            return back()->with('error', 'Gagal import data: ' . $e->getMessage());
        }
    }

    private function generateDays($year, $month)
    {
        $days = [];
        $total = Carbon::create($year, $month)->daysInMonth;

        for ($i = 1; $i <= $total; $i++) {
            $days[] = $i;
        }

        return $days;
    }

    private function getKaryawanWithAbsensi($year, $month)
    {
        // Mengambil karyawan berdasarkan kategori gaji dan dengan absensi di bulan dan tahun yang ditentukan
        $karyawanMingguan = Karyawan::whereHas('gajiKaryawan', function($query) {
            $query->where('kategori_gaji', 'Mingguan');
        })->with(['absensi' => function ($q) use ($year, $month) {
            $q->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
        }])->get();

        $karyawanBulanan = Karyawan::whereHas('gajiKaryawan', function($query) {
            $query->where('kategori_gaji', 'Bulanan');
        })->with(['absensi' => function ($q) use ($year, $month) {
            $q->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
        }])->get();

        $karyawanBelumDiketahui = Karyawan::whereDoesntHave('gajiKaryawan')
        ->with(['absensi' => function ($q) use ($year, $month) {
            $q->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month);
        }])
    ->get();
        $karyawanSemua = $karyawanMingguan->merge($karyawanBulanan)->merge($karyawanBelumDiketahui);
         $karyawanSemua = $karyawanSemua->sortBy('id')->values();
        // Kembalikan data terkelompok berdasarkan kategori gaji
        return [
            'Mingguan' => $karyawanMingguan,
            'Bulanan' => $karyawanBulanan,
            'belum_diketahui' => $karyawanBelumDiketahui,
            'Semua' => $karyawanSemua
        ];
    }

    

    public function preview(Request $request)
{
    
    $kategori_gaji = $request->get('kategori', 'all');
    $filePath = $request->get('filePath');

    // Ambil karyawan berdasarkan kategori
    $karyawanMingguan = Karyawan::whereHas('gajiKaryawan', fn($q) => $q->where('kategori_gaji', 'Mingguan'))->get();
    $karyawanBulanan = Karyawan::whereHas('gajiKaryawan', fn($q) => $q->where('kategori_gaji', 'Bulanan'))->get();
    $karyawanBelumDiketahui = Karyawan::whereHas('gajiKaryawan', fn($q) => $q->whereNull('kategori_gaji'))->get();

    // Jika tidak ada file sama sekali
    if (!$request->hasFile('file') && !$filePath) {
        return view('absensi.preview', [
            'previewData' => [],
            'karyawans' => Karyawan::all(),
            'karyawanMingguan' => $karyawanMingguan,
            'karyawanBulanan' => $karyawanBulanan,
            'karyawanBelumDiketahui' => $karyawanBelumDiketahui,
            'kategori_gaji' => $kategori_gaji,
            'filePath' => null,
            'month' => now()->month,
            'year' => now()->year,
        ]);
    }

    // Tentukan file yang digunakan
    if ($filePath && !$request->hasFile('file')) {
        $path = $filePath;
        $fullPath = Storage::disk('public')->path($path);
    } else {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('uploads/absensi', $filename, 'public');
        $fullPath = Storage::disk('public')->path($path);
    }

    try {
        $spreadsheet = IOFactory::load($fullPath);
        $sheet = $spreadsheet->getActiveSheet();
        $highestColumn = $sheet->getHighestColumn();
        $rows = $sheet->rangeToArray("A1:{$highestColumn}13", null, true, false);

        $year = now()->year;
        $month = now()->month;
        if (!empty($rows[4][0]) && stripos($rows[4][0], 'Month:') !== false) {
            if (preg_match('/Month:\s*(\d{1,2})/', $rows[4][0], $m)) {
                $month = (int) $m[1];
            }
        }

        $convertTime = function ($value) {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('H:i:s');
            }
            return !empty($value) ? (string) $value : null;
        };

        $dates1 = $rows[7] ?? [];
        $in1 = $rows[8] ?? [];
        $out1 = $rows[9] ?? [];
        $dates2 = $rows[10] ?? [];
        $in2 = $rows[11] ?? [];
        $out2 = $rows[12] ?? [];

        $previewData = [];

        foreach ([[$dates1, $in1, $out1], [$dates2, $in2, $out2]] as [$dates, $in, $out]) {
            for ($i = 0; $i < count($dates); $i++) {
                $day = (int)trim($dates[$i] ?? '');
                if ($day >= 1 && $day <= 31) {
                    $tanggal = Carbon::create($year, $month, $day)->toDateString();
                    $previewData[] = [
                        'tanggal' => $tanggal,
                        'jam_masuk' => $convertTime($in[$i] ?? null),
                        'jam_pulang' => $convertTime($out[$i] ?? null),
                    ];
                }
            }
        }

        usort($previewData, fn($a, $b) => strtotime($a['tanggal']) <=> strtotime($b['tanggal']));

        return view('absensi.preview', [
            'previewData' => $previewData,
            'karyawans' => Karyawan::all(),
            'karyawanMingguan' => $karyawanMingguan,
            'karyawanBulanan' => $karyawanBulanan,
            'karyawanBelumDiketahui' => $karyawanBelumDiketahui,
            'kategori_gaji' => $kategori_gaji,
            'filePath' => $path,
            'month' => $month,
            'year' => $year,
        ]);
    } catch (\Exception $e) {
        Log::error('Preview error: ' . $e->getMessage());
        return back()->with('error', 'Gagal melakukan pratinjau file: ' . $e->getMessage());
    }
}


    

// Contoh dalam AbsensiController
public function store(Request $request)
{
    $absensi = new Absensi;
    $absensi->karyawan_id = $request->karyawan_id;
    $absensi->tanggal = $request->tanggal;
    $absensi->jam_masuk = $request->jam_masuk;
    $absensi->jam_pulang = $request->jam_pulang;

    // Periksa apakah jam masuk dan jam pulang ada
    if ($request->jam_masuk && $request->jam_pulang) {
        $absensi->status = 'H'; // Set status menjadi Hadir jika ada jam masuk dan pulang
    }

    $absensi->save();

    return redirect()->route('absensi.index')->with('success', 'Absensi berhasil ditambahkan!');
}
public function deleteAll(Request $request, $karyawan_id)
{
    $request->validate([
        'month' => 'required|integer|min:1|max:12',
        'year' => 'required|integer|min:2000',
    ]);

    $karyawan = Karyawan::findOrFail($karyawan_id);

    $deleted = $karyawan->absensi()
        ->whereMonth('tanggal', $request->month)
        ->whereYear('tanggal', $request->year)
        ->delete();

    return redirect()->route('absensi.show', [
        'month' => $request->month,
        'year' => $request->year
    ])->with('success', 'Data absensi ' . $karyawan->nama . ' berhasil dihapus.');
}
public function showForKaryawan()
{
    // Ambil data karyawan yang sedang login
    $karyawan = auth()->user()->karyawan; // Mengambil data karyawan yang sedang login (asumsi relasi sudah ada)

    // Ambil data absensi untuk karyawan tersebut
    $year = now()->year;
    $month = now()->month;

    $absensi = Absensi::where('karyawan_id', $karyawan->id)
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->get();

    // Ambil semua hari dalam bulan ini
    $days = $this->generateDays($year, $month);

    return view('absensi.karyawan', compact('karyawan', 'absensi', 'year', 'month', 'days'));
}







}
