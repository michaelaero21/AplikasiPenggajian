<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    Controller, KaryawanController, GajiKaryawanController, HomeController,
    Auth\LoginController, Auth\RegisterController, Auth\ProfileController,
    AdminController, PegawaiController, AbsensiController,
    KaryawanDashboardController, SlipGajiController, LaporanController
};
use Illuminate\Support\Str;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }
    $role = Str::lower(trim(auth()->user()->role));
    return $role === 'admin'
        ? redirect()->route('home')
        : redirect()->route('karyawan.dashboard');
});

// === Auth Routes ===
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login.form');
Route::post('login', [LoginController::class, 'login'])->name('login');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');
// Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
// Route::post('register', [RegisterController::class, 'register']);

// === Home Route setelah login ADMIN ===
Route::middleware(['auth','check.active', 'role:admin'])
      ->get('/home', [HomeController::class, 'index'])
      ->name('home');
// === Profile (ADMIN) ===
Route::middleware(['auth','check.active','role:admin'])->group(function () {
    Route::get('/profile',        [ProfileController::class, 'show']) ->name('profile.show');
    Route::get('/profile/edit',   [ProfileController::class, 'edit']) ->name('profile.edit');
    Route::post('/profile/update',[ProfileController::class, 'update'])->name('profile.update');


// =============================================
// ========== ROUTE UNTUK BAGIAN ADMIN SAJA ===========
// =============================================
    // DATA Karyawan (ADMIN)
    Route::resource('karyawan', KaryawanController::class);
    Route::get('/karyawan/search', [KaryawanController::class, 'search'])->name('karyawan.search');
    Route::put('/karyawan/{karyawan}/reset-password', [KaryawanController::class, 'resetPassword'])
    ->name('karyawan.resetPassword');

    // DATA Gaji (ADMIN)
    Route::resource('gaji', GajiKaryawanController::class);

    // DATA Absensi (ADMIN)
    Route::prefix('absensi')->group(function () {
        Route::get('/', [AbsensiController::class, 'index'])->name('absensi.index');
        Route::get('/show', [AbsensiController::class, 'showAbsensi'])->name('absensi.show');
        Route::post('/upload', [AbsensiController::class, 'upload'])->name('absensi.upload');
        Route::match(['get', 'post'], '/preview', [AbsensiController::class, 'preview'])->name('absensi.preview');
        Route::post('/import', [AbsensiController::class, 'import'])->name('absensi.import');
        Route::get('/import/{karyawan_id}', [AbsensiController::class, 'importAbsensi'])->name('absensi.import.karyawan');
        Route::delete('/{karyawan_id}/hapus-semua', [AbsensiController::class, 'deleteAll'])->name('absensi.deleteAll');
        Route::get('/{karyawan}/imported', [AbsensiController::class, 'showImported'])->name('absensi.imported');
        Route::get('/{karyawan}/edit', [AbsensiController::class, 'showEditForm'])->name('absensi.edit');
        Route::post('{oldKaryawan}/update', [AbsensiController::class, 'editAbsensi'])->name('absensi.update');
    });

    // MENU Slip Gaji (ADMIN)
    Route::prefix('slip-gaji')->name('slip-gaji.')->controller(SlipGajiController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/preview/{slipGaji}', 'previewPdf')->name('preview');
        Route::get('/download/{slipGaji}', 'downloadPdf')->name('download');
        Route::post('/kirim-wa/{slipGaji}', 'kirimWhatsapp')->name('kirim_wa');
        Route::post('/generate-slip', 'generateSlipFromIndex')->name('generate');
        Route::get('/manual-hitung', 'formManualHitung')->name('manual_form');
        Route::post('/manual-hitung', 'prosesManualHitung')->name('manual_proses');
        Route::post('/hitung-otomatis', 'hitungOtomatis')->name('hitung_otomatis');
        Route::post('/generate-massal', 'generateMassal')->name('generate_massal');
        Route::post('/download-massal', 'downloadMassal')->name('download_massal');
        Route::post('/kirim-wa-massal', 'kirimMassal')->name('kirim_wa_massal');
        Route::post('/thr/set', 'setThrFlag')->name('setThrFlag');
        Route::post('set-thr-flag-massal', [SlipGajiController::class, 'setThrFlagMassal'])
         ->name('setThrFlagMassal');
        Route::get('/slip-gaji/table-all', [SlipGajiController::class, 'tableAll'])
     ->name('slip-gaji.table-all');



    });

    // Laporan
    Route::get('/laporan/slip-gaji', [LaporanController::class, 'index'])->name('laporan.slip-gaji');
});


// ===================================================
// ========== ROUTE UNTUK APLIKASI KARYAWAN ==========
// ===================================================
Route::middleware(['auth', 'check.active', 'role:karyawan'])->group(function () {

    Route::get('/dashboard-karyawan', [KaryawanDashboardController::class, 'index'])
        ->name('karyawan.dashboard');

    Route::get('/absensi/karyawan', [AbsensiController::class, 'showForKaryawan'])
        ->name('absensi.karyawan');

    Route::get('/slip-gaji-karyawan', [SlipGajiController::class, 'indexKaryawan'])
          ->name('slip-gaji.karyawan');

    Route::get('/slip-gaji-karyawan/preview/{slipGaji}',
          [SlipGajiController::class, 'previewPdfKaryawan'])
          ->name('slip-gaji.karyawan.preview');
          
    Route::get('/slip-gaji-karyawan/download/{slipGaji}',
          [SlipGajiController::class, 'downloadPdfKaryawan'])
          ->name('slip-gaji.karyawan.download');
    Route::get('/profile/karyawan/show',
        [ProfileController::class, 'showKaryawan'])
        ->name('profile.show.karyawan');
    Route::get('/profile/karyawan/edit',
        [ProfileController::class, 'editKaryawan'])
        ->name('profile.edit.karyawan');
    Route::post('/profile/karyawan/update',
        [ProfileController::class, 'updateKaryawan'])
        ->name('profile.karyawan.update');
});

