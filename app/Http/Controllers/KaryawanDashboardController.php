<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use App\Models\Gaji;

class KaryawanDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Ambil data absensi dan gaji untuk karyawan yang sedang login
        $absensis = Absensi::where('karyawan_id', $user->id)->orderBy('tanggal', 'desc')->get();
        $gajis = Gaji::where('karyawan_id', $user->id)->orderBy('periode', 'desc')->get();

        return view('karyawan.dashboard', compact('absensis', 'gajis'));
    }
}
