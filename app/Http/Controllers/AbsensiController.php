<?php

namespace App\Http\Controllers;
use App\Models\Karyawan; // Import Karyawan model
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::with('absensi')->get();
        $year = date('Y');  // Menetapkan tahun saat ini
        return view('absensi.index', compact('karyawans', 'year'));
    }


    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $file = $request->file('file');
        // Simpan file atau proses import di sini

        return back()->with('success', 'File berhasil diupload.');
    }
    public function dashboard()
    {
        $timezone = 'Asia/Jakarta';
        $today = \Carbon\Carbon::now($timezone)->startOfDay();
        $tanggalGajian = \Carbon\Carbon::create($today->year, $today->month, 25, 0, 0, 0, $timezone)->startOfDay();
    
        if ($today->gt($tanggalGajian)) {
            $tanggalGajian->addMonth();
        }
    
        $selisihHari = $today->diffInDays($tanggalGajian, false);
        $waktuGajian = $tanggalGajian->diffInSeconds($today, false);
        
        return view('home', compact('selisihHari', 'waktuGajian', 'tanggalGajian'));
    }
    

}
