<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Batasi akses method create, store, edit, update, destroy hanya untuk non-karyawan
        $this->middleware(function ($request, $next) {
            $user = auth()->user();

            // Jika user adalah karyawan dan mencoba akses selain index/show, tolak
            if (
                in_array($request->route()->getActionMethod(), ['create', 'store', 'edit', 'update', 'destroy']) &&
                $user->role === 'Karyawan'
            ) {
                return redirect()->route('home')->with('error', 'Akses ditolak: Karyawan tidak boleh mengubah data.');
            }

            return $next($request);
        });
    }
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
    public function show($id)
    {
        // Ambil karyawan berdasarkan ID yang ada di parameter route
        $karyawan = Karyawan::findOrFail($id);

        // Pastikan karyawan yang sedang login hanya dapat melihat data miliknya
        if (auth()->user()->id != $karyawan->id) {
            return redirect()->route('home')->with('error', 'Akses ditolak: Data tidak sesuai');
        }

        // Tampilkan data karyawan ke view
        return view('karyawan.show', compact('karyawan'));
    }


}
