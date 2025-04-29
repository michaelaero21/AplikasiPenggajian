<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GajiKaryawan;
use App\Models\Karyawan;

class GajiKaryawanController extends Controller
{
    public function index()
    {
        $gajiKaryawan = GajiKaryawan::with('karyawan')->get();
        return view('gaji.index', compact('gajiKaryawan'));
    }

    public function create()
    {
        $karyawan = Karyawan::all();
        return view('gaji.create', compact('karyawan'));
    }

    public function store(Request $request)
    {
        // Validasi jika gaji karyawan sudah ada
        if ($this->gajiAlreadyExists($request->karyawan_id)) {
            return redirect()->route('gaji.create')->with('error', 'Gaji untuk karyawan ini sudah ada.');
        }

        $this->validateGaji($request);

        $karyawan = Karyawan::findOrFail($request->karyawan_id);
        $data = $this->buildGajiData($request, $karyawan->jabatan);

        GajiKaryawan::create($data);

        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $gajiKaryawan = GajiKaryawan::with('karyawan')->findOrFail($id);
        $karyawan = Karyawan::where('id', $gajiKaryawan->karyawan_id)->get();

        return view('gaji.edit', compact('gajiKaryawan', 'karyawan'));
    }

    public function update(Request $request, $id)
    {
        $gajiKaryawan = GajiKaryawan::findOrFail($id);

        // Validasi jika gaji sudah ada untuk karyawan yang lain
        if ($this->gajiAlreadyExists($request->karyawan_id) && $gajiKaryawan->karyawan_id != $request->karyawan_id) {
            return redirect()->route('gaji.edit', $id)->with('error', 'Gaji untuk karyawan ini sudah ada.');
        }

        if ($gajiKaryawan->karyawan_id != $request->karyawan_id) {
            return redirect()->route('gaji.index')->with('error', 'Tidak dapat mengubah data karyawan.');
        }

        $this->validateGaji($request);

        $karyawan = $gajiKaryawan->karyawan;
        $data = $this->buildGajiData($request, $karyawan->jabatan);

        $gajiKaryawan->update($data);

        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gajiKaryawan = GajiKaryawan::findOrFail($id);
        $gajiKaryawan->delete();

        return redirect()->route('gaji.index')->with('success', 'Gaji karyawan berhasil dihapus.');
    }

    protected function validateGaji(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'gaji_pokok' => 'required|numeric',
            'uang_makan' => 'nullable|numeric',
            'asuransi' => 'nullable|numeric',
            'uang_transportasi' => 'nullable|numeric',
            'uang_lembur' => 'nullable|numeric',
            'thr' => 'nullable|numeric',
            'tunjangan_sewa_transport' => 'nullable|numeric',
            'tunjangan_pulsa' => 'nullable|numeric',
            'insentif' => 'nullable|numeric',
        ]);
    }

    protected function buildGajiData(Request $request, $jabatan)
    {
        $data = [
            'karyawan_id' => $request->karyawan_id,
            'kategori_gaji' => $request->kategori_gaji,
            'gaji_pokok' => $request->gaji_pokok,
            'uang_makan' => $request->uang_makan,
            'asuransi' => $request->asuransi,
            'uang_transportasi' => $request->uang_transportasi ?? 0,  // Pastikan ada nilai default
            'uang_lembur' => $request->uang_lembur ?? 0,  // Nilai default jika tidak ada input
            'thr' => $request->thr ?? 0,  // Nilai default jika tidak ada input
        ];
    
        if ($jabatan === 'Marketing') {
            $data['tunjangan_sewa_transport'] = $request->tunjangan_sewa_transport ?? 0;
            $data['tunjangan_pulsa'] = $request->tunjangan_pulsa ?? 0;
            $data['insentif'] = $request->insentif ?? 0;
        }
    
        return $data;
    }

    // Fungsi untuk memeriksa apakah gaji untuk karyawan sudah ada
    protected function gajiAlreadyExists($karyawanId)
    {
        return GajiKaryawan::where('karyawan_id', $karyawanId)->exists();
    }
}
