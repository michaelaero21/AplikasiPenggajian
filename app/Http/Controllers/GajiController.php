<?php

namespace App\Http\Controllers;

use App\Models\GajiKaryawan;
use Illuminate\Http\Request;

class GajiController extends Controller
{
    // Menampilkan daftar gaji karyawan
    public function index()
    {
        // Mengambil semua data gaji karyawan
        $gajiKaryawans = GajiKaryawan::all();
        
        return view('gaji.index', compact('gajiKaryawans'));
    }
}
