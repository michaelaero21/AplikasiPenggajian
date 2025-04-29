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

Route::middleware(['auth', 'pegawai'])->group(function () {
    Route::get('/pegawai/dashboard', [PegawaiController::class, 'index'])->name('pegawai.dashboard');
});

// Profile route dengan middleware auth
// Profile route dengan middleware auth
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
});

// Menampilkan halaman daftar absensi
Route::get('/absensi', [AbsensiController::class, 'index'])->name('absensi.index');

// Upload file Excel / CSV absensi
Route::post('/absensi/upload', [AbsensiController::class, 'upload'])->name('absensi.upload');
