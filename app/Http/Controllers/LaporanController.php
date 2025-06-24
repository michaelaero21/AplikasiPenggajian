<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SlipGaji; 
class LaporanController extends Controller
{
    public function index(Request $request)
{
    $kategori = $request->input('kategori_gaji');
    $start = $request->input('start_date');
    $end = $request->input('end_date');

    $query = SlipGaji::with(['karyawan']);

    if ($kategori && $kategori !== 'semua') {
        $query->where('kategori_gaji', $kategori);
    }

    if ($start && $end) {
        $query->whereBetween('created_at', [$start, $end]);
    }

    $data = $query->orderBy('created_at', 'desc')->get();

    $total = $data->sum('total_dibayar');

    return view('laporan.index', compact('data', 'total', 'kategori', 'start', 'end'));
}

}
