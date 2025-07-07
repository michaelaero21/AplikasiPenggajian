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
        $search = $request->input('search'); // tambahkan search

        $query = SlipGaji::with('karyawan');

        // Filter kategori gaji
        if ($kategori && $kategori !== 'semua') {
            $query->whereHas('karyawan', function ($q) use ($kategori) {
                $q->where('kategori_gaji', $kategori);
            });
        }

        // Filter tanggal
        if ($start && $end) {
            $query->whereBetween('created_at', [$start, $end]);
        }

        // Filter nama karyawan
        if ($search) {
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        }

        $allData = $query->orderBy('created_at', 'desc')->get();

        // Kelompokkan data berdasarkan karyawan
        // Kelompokkan data berdasarkan karyawan_id dan urutkan berdasarkan ID karyawan
    $grouped = $allData->groupBy('karyawan_id')
        ->map(function ($slips) {
            return [
                'karyawan' => $slips->first()->karyawan,
                'slips' => $slips,
                'total_gaji' => $slips->sum('total_dibayar'),
                'total_tunjangan' => $slips->sum('tunjangan'),
                'total_lembur' => $slips->sum('lembur'),
                'total_thr' => $slips->sum('thr'),
                'total_insentif' => $slips->sum('insentif'),
                'total_gaji_pokok' => $slips->sum('gaji_pokok'),
            ];
        })
        ->sortBy(function ($item) {
            return $item['karyawan']->id; // urutkan berdasarkan ID karyawan
        });


        // Total berdasarkan data yang difilter
        $total = $allData->sum('total_dibayar');

        return view('laporan.index', [
            'data' => $grouped,
            'total' => $total,
            'kategori' => $kategori,
            'start' => $start,
            'end' => $end,
            'search' => $search, // dikirim ke view jika perlu
        ]);
    }
}   