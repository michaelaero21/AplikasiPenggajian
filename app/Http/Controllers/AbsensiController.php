<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Absensi;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with('absensi')->get();
        $year = date('Y');
        $month = date('m');
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = Carbon::createFromDate($year, $month, $i)->format('Y-m-d');
        }

        return view('absensi.index', compact('karyawans', 'year', 'month', 'days'));
    }

    public function showAbsensi(Request $request)
    {
        $month = $request->get('month', date('F'));
        $year = $request->get('year', date('Y'));
        $monthNumber = date('m', strtotime($month));

        $karyawans = Karyawan::with(['absensi' => function ($query) use ($monthNumber, $year) {
            $query->whereMonth('tanggal', $monthNumber)
                  ->whereYear('tanggal', $year);
        }])->get();

        $daysInMonth = Carbon::create($year, $monthNumber)->daysInMonth;
        $days = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $days[] = Carbon::createFromDate($year, $monthNumber, $i)->format('Y-m-d');
        }

        return view('absensi.index', compact('karyawans', 'month', 'year', 'days'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        $file = $request->file('file');
        $filePath = $file->storeAs('uploads/absensi', $file->getClientOriginalName());

        return back()->with('success', 'File berhasil diupload.')->with('filePath', $filePath);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        $filePath = $request->file('file')->getRealPath();

        DB::beginTransaction();
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $currentID = null;
            $currentName = null;

            foreach ($rows as $row) {
                if (isset($row[0]) && str_contains($row[0], 'ID:')) {
                    preg_match('/ID:\s*(\d+)\s+Name:\s*(.+)/i', $row[0], $matches);
                    if ($matches) {
                        $currentID = (int) $matches[1];
                        $currentName = trim($matches[2]);

                        $karyawan = Karyawan::where('id', $currentID)
                            ->where('nama', 'like', "%{$currentName}%")
                            ->first();

                        if (!$karyawan) {
                            $currentID = null;
                        }
                    }
                }

                if ($currentID && isset($row[0]) && is_numeric($row[0])) {
                    $day = (int) $row[0];
                    for ($i = 1; $i < count($row); $i += 2) {
                        $jamMasuk = $row[$i] ?? null;
                        $jamPulang = $row[$i + 1] ?? null;

                        if ($jamMasuk && $jamPulang) {
                            $tanggal = Carbon::createFromDate(now()->year, now()->month, $day)->format('Y-m-d');

                            Absensi::updateOrCreate(
                                [
                                    'karyawan_id' => $currentID,
                                    'tanggal' => $tanggal,
                                ],
                                [
                                    'status' => "{$jamMasuk} - {$jamPulang}",
                                ]
                            );
                        }
                    }
                }
            }

            DB::commit();
            return back()->with('success', 'Data absensi berhasil diimport.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $timezone = 'Asia/Jakarta';
        $today = Carbon::now($timezone)->startOfDay();
        $tanggalGajian = Carbon::create($today->year, $today->month, 25, 0, 0, 0, $timezone)->startOfDay();

        if ($today->gt($tanggalGajian)) {
            $tanggalGajian->addMonth();
        }

        $selisihHari = $today->diffInDays($tanggalGajian, false);
        $waktuGajian = $tanggalGajian->diffInSeconds($today, false);

        return view('home', compact('selisihHari', 'waktuGajian', 'tanggalGajian'));
    }
}
