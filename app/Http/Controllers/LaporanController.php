<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SlipGaji; 
class LaporanController extends Controller
{
   public function index(Request $request)
{
    // dd($request->start_date, $request->end_date);

     $start = $request->start_date ? \Carbon\Carbon::parse($request->start_date) : null;
    $end = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : null;

    // Validasi: Jika tanggal awal lebih besar dari tanggal akhir
    if ($start && $end && $start->gt($end)) {
        return redirect()->back()->withInput()->with('error', 'Tanggal awal tidak boleh lebih besar dari tanggal akhir.');
    }

    $slips = SlipGaji::with('karyawan')
        ->when($request->search, function ($query, $search) {
            $query->whereHas('karyawan', function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%');
            });
        })
        ->when($request->kategori_gaji && $request->kategori_gaji != 'semua', function ($query) use ($request) {
            $query->whereHas('karyawan', function ($q) use ($request) {
                $q->where('kategori_gaji', $request->kategori_gaji);
            });
        })
        ->when($start && $end, function ($query) use ($start, $end, $request) {
            $kategori = $request->kategori_gaji;

            $query->where(function ($q) use ($kategori, $start, $end) {
                if ($kategori == 'bulanan') {
                    $q->whereBetween('periode', [$start->format('Y-m'), $end->format('Y-m')]);
                } elseif ($kategori == 'mingguan') {
                    $q->whereBetween('periode', [$start->toDateString(), $end->toDateString()]);
                }
            });
        })
        ->orderBy('created_at', 'desc')
        ->get();    // Kelompokkan berdasarkan karyawan_id
    $grouped = $slips->groupBy('karyawan_id')->map(function ($slips) {
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
            'total_dibayar' => $slips->sum('total_dibayar'),
        ];
    })->sortBy(fn($item) => $item['karyawan']->id);

    $total_semua = $slips->sum('total_dibayar');

    return view('laporan.index', [
        'data' => $grouped,
        'total' => $total_semua,
        'kategori' => $request->kategori_gaji,
        'start' => $request->start_date,
        'end' => $request->end_date,
        'search' => $request->search,
    ]);
}

}