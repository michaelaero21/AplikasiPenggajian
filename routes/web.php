<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\GajiKaryawanController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\KaryawanDashboardController;
use App\Http\Controllers\SlipGajiController;
use App\Http\Controllers\LaporanController;

Route::get('/', function () {
    // Mengarahkan pengguna yang belum login ke halaman login
    return auth()->check() ? redirect()->route('home') : redirect()->route('login');
});

// Route untuk halaman home setelah login
Route::middleware('auth')->get('/home', [HomeController::class, 'index'])->name('home');

// Route untuk Karyawan
Route::middleware('auth')->resource('karyawan', KaryawanController::class); // Menambahkan middleware auth
Route::middleware('auth')->get('/karyawan/search', [KaryawanController::class, 'search'])->name('karyawan.search');

// Route untuk Gaji Karyawan
Route::middleware('auth')->resource('gaji', GajiKaryawanController::class); // Menambahkan middleware auth

// Route untuk Login & Logout
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Route untuk Register
Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [RegisterController::class, 'register']);

// Middleware untuk Admin dan Pegawai
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// Middleware untuk Karyawan Dashboard
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard-karyawan', [KaryawanDashboardController::class, 'index'])->name('karyawan.dashboard');
});

// Profile route dengan middleware auth
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Route untuk Absensi
Route::middleware(['auth'])->prefix('absensi')->group(function () {
    Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');
    Route::get('/show', [AbsensiController::class, 'showAbsensi'])->name('absensi.show');
    Route::post('/upload', [AbsensiController::class, 'upload'])->name('absensi.upload');

    // 🔄 Ubah nama agar tidak konflik (GET untuk filter preview)
    Route::get('/preview', [AbsensiController::class, 'preview'])->name('absensi.preview');
    
    // POST preview (setelah upload file)
    Route::post('/preview', [AbsensiController::class, 'preview'])->name('absensi.preview');

    // POST untuk menyimpan ke DB
    Route::post('/import', [AbsensiController::class, 'import'])->name('absensi.import');

    // Jika kamu perlu import berdasarkan karyawan_id via GET (opsional)
    Route::get('/import/{karyawan_id}', [AbsensiController::class, 'importAbsensi'])->name('absensi.import.karyawan');

    Route::delete('/{karyawan_id}/hapus-semua', [AbsensiController::class, 'deleteAll'])->name('absensi.deleteAll');
    Route::get('/karyawan', [AbsensiController::class, 'showForKaryawan'])->name('absensi.karyawan');
});

// Middleware untuk Admin dan Karyawan
Route::middleware(['auth', 'check.admin.access'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});
Route::middleware(['auth', 'check.karyawan.access'])->group(function () {
    Route::get('/absensi/karyawan', [AbsensiController::class, 'showForKaryawan'])->name('absensi.karyawan');
});

// Custom Middleware Check Admin Access
Route::middleware(['auth', 'check.admin.access'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
});

// Custom Middleware Check Karyawan Access
Route::middleware(['auth', 'check.karyawan.access'])->group(function () {
    Route::get('/absensi/karyawan', [AbsensiController::class, 'showForKaryawan'])->name('absensi.karyawan');
});


Route::prefix('slip-gaji')->name('slip-gaji.')->controller(SlipGajiController::class)->group(function () {
    // Halaman utama slip gaji
    Route::get('/', 'index')->name('index'); // URL: /slip-gaji
    
    // Preview, download, kirim WA per slip
    Route::get('/preview/{slipGaji}', 'previewPdf')->name('preview');
    Route::get('/download/{slipGaji}', 'downloadPdf')->name('download');
    Route::post('/kirim-wa/{slipGaji}', 'kirimWhatsapp')->name('kirim_wa');

    // Generate slip satuan (dari tombol di table)
    
    Route::post('/generate-slip', 'generateSlipFromIndex')->name('generate');

    // Manual dan otomatis hitung gaji
    Route::get('/manual-hitung', 'formManualHitung')->name('manual_form');
    Route::post('/manual-hitung', 'prosesManualHitung')->name('manual_proses');
    Route::post('/hitung-otomatis', 'hitungOtomatis')->name('hitung_otomatis');

    // 🔽 Route aksi massal
    Route::post('/generate-massal', 'generateMassal')->name('generate_massal');
    Route::post('/download-massal', 'downloadMassal')->name('download_massal');
    Route::post('/kirim-wa-massal', 'kirimMassal')->name('kirim_wa_massal');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/laporan/slip-gaji', [LaporanController::class, 'index'])->name('laporan.slip-gaji');
});


