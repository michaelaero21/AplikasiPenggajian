<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;

class HomeController extends Controller
{
    public function index()
    {
        $totalKaryawan = Karyawan::count();
        $gajiPerKaryawan = 0; // Sesuaikan dengan gaji pokok per karyawan
        $totalGaji = $totalKaryawan * $gajiPerKaryawan; // Total gaji semua karyawan

        return view('home', compact('totalKaryawan', 'totalGaji'));
    }
}
