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
        $search = $request->input('search');

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

        // Kelompokkan dan total seluruh komponen slip
        $grouped = $allData->groupBy('karyawan_id')->map(function ($slips) {
            return [
                'karyawan' => $slips->first()->karyawan,
                'slips' => $slips,
                'total_gaji_pokok' => $slips->sum('gaji_pokok'),
                'total_uang_makan' => $slips->sum('uang_makan'),
                'total_transport' => $slips->sum('uang_transport'),
                'total_lembur' => $slips->sum('lembur'),
                'total_thr' => $slips->sum('thr'),
                'total_tunjangan_sewa' => $slips->sum('tunjangan_sewa'),
                'total_tunjangan_pulsa' => $slips->sum('tunjangan_pulsa'),
                'total_insentif' => $slips->sum('insentif'),
                'total_bpjs' => $slips->sum('asuransi'),
                'total_dibayar' => $slips->sum('total_dibayar'), // total keseluruhan
            ];
        })->sortBy(fn($item) => $item['karyawan']->id);

        $total_semua = $allData->sum('total_dibayar');

        return view('laporan.index', [
            'data' => $grouped,
            'total' => $total_semua,
            'kategori' => $kategori,
            'start' => $start,
            'end' => $end,
            'search' => $search,
        ]);
    }
}