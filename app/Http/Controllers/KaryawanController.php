<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    public function index()
    {
        $karyawans = Karyawan::all();
        return view('karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        $jabatanList = ['Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 'Gudang', 'Helper Gudang', 'Office Girl'];
        return view('karyawan.create', compact('jabatanList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|in:Accounting,Admin,Admin Penjualan,Finance,Admin Purchasing,Head Gudang,Admin Gudang,Supervisor,Marketing,Driver,Gudang,Helper Gudang,Office Girl',
            'nomor_telepon' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat_karyawan' => 'required|string',
        ]);

        // Cek apakah data karyawan dengan kombinasi ini sudah ada
        $existing = Karyawan::where('nama', $request->nama)
            ->where('jabatan', $request->jabatan)
            ->where('nomor_telepon', $request->nomor_telepon)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data karyawan dengan detail yang sama sudah ada. Tidak dapat menambahkan duplikat.');
        }

        Karyawan::create($request->all());

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        $jabatanList = ['Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 'Gudang', 'Helper Gudang', 'Office Girl'];
        return view('karyawan.edit', compact('karyawan', 'jabatanList'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|in:Accounting,Admin,Admin Penjualan,Finance,Admin Purchasing,Head Gudang,Admin Gudang,Supervisor,Marketing,Driver,Gudang,Helper Gudang,Office Girl',
            'nomor_telepon' => 'required|string|max:20',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat_karyawan' => 'required|string',
        ]);

        // Cek apakah data duplikat sudah ada (selain dari ID yang sedang diedit)
        $existing = Karyawan::where('nama', $request->nama)
            ->where('jabatan', $request->jabatan)
            ->where('nomor_telepon', $request->nomor_telepon)
            ->where('id', '!=', $karyawan->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Data karyawan dengan detail yang sama sudah ada. Tidak dapat memperbarui menjadi duplikat.');
        }

        $karyawan->update($request->only(['nama', 'jabatan', 'nomor_telepon', 'jenis_kelamin', 'alamat_karyawan']));

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
