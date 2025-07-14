<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class KaryawanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.active']);

        // Batasi akses method create, store, edit, update, destroy hanya untuk non‑karyawan
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
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
        $karyawans = Karyawan::with('user')->get();
        return view('karyawan.index', compact('karyawans'));
    }

    public function create()
    {
        $jabatanList = ['Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 'Gudang', 'Helper Gudang', 'Office Girl'];
        return view('karyawan.create', compact('jabatanList'));
    }

    /**
     * Simpan data karyawan baru + akun user.
     */
    public function store(Request $request)
    {
        /* 1. Validasi */
        $request->validate([
            'nama'            => 'required|string|max:255',
            'jabatan'         => 'required|string|in:Accounting,Admin,Admin Penjualan,Finance,Admin Purchasing,Head Gudang,Admin Gudang,Supervisor,Marketing,Driver,Gudang,Helper Gudang,Office Girl',
            'nomor_telepon'   => 'required|string|max:20',
            'jenis_kelamin'   => 'required|in:Laki-laki,Perempuan',
            'alamat_karyawan' => 'required|string',
        ]);

    $duplicate = Karyawan::where('nama', $request->nama)
        ->where('jabatan', $request->jabatan)
        ->where('nomor_telepon', $request->nomor_telepon)
        ->first();

    if ($duplicate) {
        return back()->withInput()->with('error', 'Data karyawan dengan nama, jabatan, dan nomor telepon ini sudah ada di sistem.');
    }

    // 3. Baru cek unique nomor telepon saja
    if (Karyawan::where('nomor_telepon', $request->nomor_telepon)->exists()) {
        return back()->withInput()->with('error', 'Nomor telepon ini sudah digunakan oleh karyawan lain.');
    }
        /* 3. Buat username unik dari nama */
        $baseUsername = Str::slug($request->nama, '_');
        $username     = $baseUsername;
        $i            = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $i++;
        }

        /* 4. Simpan dalam transaksi */
        DB::transaction(function () use ($request, $username) {
            /* 4a. Tentukan role: jika jabatan Admin maka role Admin, else Karyawan */
            $role = ($request->jabatan === 'Admin') ? 'Admin' : 'Karyawan';

            /* 4b. Buat user */
            $user = User::create([
                'name'              => $request->nama,
                'username'          => $username,
                'alamat'            => $request->alamat_karyawan,
                'nomor_telepon'     => $request->nomor_telepon,
                'password'          => Hash::make($username),
                'role'              => $role,
                'status'            => 'Aktif',
                'waktu_diaktifkan'  => now(),
                'waktu_dinonaktifkan' => null,
            ]);

            /* 4c. Buat karyawan & relasi */
            Karyawan::create($request->only([
                    'nama', 'jabatan', 'nomor_telepon',
                    'jenis_kelamin', 'alamat_karyawan'
                ]) + ['user_id' => $user->id]);
        });

        return redirect()->route('karyawan.index')
            ->with('success', 'Karyawan & akun berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        $jabatanList = ['Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 'Gudang', 'Helper Gudang', 'Office Girl'];
        return view('karyawan.edit', compact('karyawan', 'jabatanList'));
    }

    /**
     * Perbarui data karyawan + sinkronisasi ke tabel users.
     */
    public function update(Request $request, Karyawan $karyawan)
    {
        $request->validate([
            'nama'            => 'required|string|max:255',
            'jabatan'         => 'required|string|in:Accounting,Admin,Admin Penjualan,Finance,Admin Purchasing,Head Gudang,Admin Gudang,Supervisor,Marketing,Driver,Gudang,Helper Gudang,Office Girl',
            'nomor_telepon'   => 'required|string|max:20|unique:karyawans,nomor_telepon,' . $karyawan->id,
            'jenis_kelamin'   => 'required|in:Laki-laki,Perempuan',
            'alamat_karyawan' => 'required|string',
            'status'          => 'required|in:Aktif,Nonaktif',
        ],[
            'nomor_telepon.unique' => 'Nomor telepon ini sudah digunakan oleh karyawan lain.',
        ]);

        // 1. Cek kombinasi nama + jabatan + nomor telepon
        $duplicate = Karyawan::where('nama', $request->nama)
            ->where('jabatan', $request->jabatan)
            ->where('nomor_telepon', $request->nomor_telepon)
            ->where('id', '!=', $karyawan->id) // penting
            ->first();

        if ($duplicate) {
            return back()->withInput()->with('error', 'Data karyawan dengan nama, jabatan, dan nomor telepon ini sudah ada di sistem.');
        }

        // 2. Cek nomor telepon sudah dipakai karyawan lain
        if (Karyawan::where('nomor_telepon', $request->nomor_telepon)
            ->where('id', '!=', $karyawan->id) // penting
            ->exists()) {
            return back()->withInput()->with('error', 'Nomor telepon ini sudah digunakan oleh karyawan lain.');
        }

        DB::transaction(function () use ($request, $karyawan) {
            /* 2. Update tabel karyawan */
            $karyawan->update($request->only([
                'nama', 'jabatan', 'nomor_telepon', 'jenis_kelamin', 'alamat_karyawan', 'status'
            ]));

            /* 3. Update tabel users */
            $user = $karyawan->user;
            if ($user) {
                $prevStatus = $user->status;                 // status sebelum update
                $newStatus  = $request->status;
                $roleBaru = ($request->jabatan === 'Admin') ? 'Admin' : 'Karyawan';

                $userUpdate = [
                    'name'          => $request->nama,
                    'alamat'        => $request->alamat_karyawan,
                    'nomor_telepon' => $request->nomor_telepon,
                    'role'          => $roleBaru,
                    'status'        => $newStatus,
                ];

                /* Transisi status ───────────────────────────────────────── */
                if ($prevStatus === 'Aktif' && $newStatus === 'Nonaktif') {
                    // Baru dinonaktifkan
                    $userUpdate['waktu_dinonaktifkan'] = now();
                    $userUpdate['waktu_diaktifkan']     = null;
                } elseif ($prevStatus === 'Nonaktif' && $newStatus === 'Aktif') {
                    // Baru diaktifkan kembali
                    $userUpdate['waktu_diaktifkan']     = now();
                    $userUpdate['waktu_dinonaktifkan']  = null;
                }
                $user->update($userUpdate);
            }
        });

        return redirect()->route('karyawan.index')->with('success', 'Data karyawan & user berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();
        return redirect()->route('karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }

    /**
     * Tampilkan detail karyawan (hanya untuk dirinya sendiri jika role karyawan).
     */
    public function show($id)
    {
        $karyawan = Karyawan::findOrFail($id);

        // Jika yang login karyawan, pastikan hanya melihat datanya sendiri
        if (auth()->user()->role === 'Karyawan' && auth()->user()->id !== $karyawan->user_id) {
            return redirect()->route('home')->with('error', 'Akses ditolak: Data tidak sesuai.');
        }

        return view('karyawan.show', compact('karyawan'));
    }

    /**
     * Reset password user ke username (huruf kecil & underscore).
     */
    public function resetPassword(Karyawan $karyawan)
    {
        $username = Str::slug($karyawan->nama, '_');
        $user = $karyawan->user;

        $user->update([
            'password' => Hash::make($username)
        ]);

        return back()->with('success', 'Password berhasil di‑reset ke default.');
    }
}
