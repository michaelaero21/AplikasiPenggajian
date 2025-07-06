<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\SlipGaji;
Use Carbon\Carbon;
class KaryawanDashboardController extends Controller
{
    public function index()
{
    $user     = Auth::user();
    $karyawan = $user->karyawan;

    if (!$karyawan) {
        return view('karyawan.dashboard')
               ->with('message', 'Profil karyawan belum dibuat.');
    }

    // --- Ambil slip terakhir dari database langsung, berdasarkan periode
    $slipTerakhir = SlipGaji::where('karyawan_id', $karyawan->id)
                            ->latest('periode')
                            ->first();

    // Pastikan tidak null agar tidak error
    $gajiDiterima = $slipTerakhir ? $slipTerakhir->total_dibayar : 0;

    // --- Tanggal gajian berikutnya (tgl 1 setiap bulan)
    $today           = Carbon::now('Asia/Jakarta')->startOfDay();
    $tanggalGajian   = Carbon::create($today->year, $today->month, 1)->startOfDay();

    if ($today->gt($tanggalGajian)) {
        $tanggalGajian->addMonth(); // Gajian bulan depan
    }

    $selisihHari      = $today->diffInDays($tanggalGajian, false);
    $tanggalGajianIso = $tanggalGajian->toIso8601String();
    if ($selisihHari % 2 === 1) {
    $selisihHari++;                // jadikan genap terdekat di atasnya
}
    $selisihHari = (int) $selisihHari;  
    $tanggalGajianIso = $tanggalGajian->toIso8601String();
    // Kirim ke view
    return view('karyawan.dashboard', compact(
        'karyawan',
        'slipTerakhir',
        'gajiDiterima',
        'selisihHari',
        'tanggalGajian',
        'tanggalGajianIso'
    ));
}

}
