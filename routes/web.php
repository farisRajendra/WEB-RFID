<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\JamKerjaController;
use App\Http\Controllers\LaporanAbsensiController;
use App\Http\Controllers\Api\AbsensiController;
use App\Http\Controllers\IzinController;



// Redirect root ke halaman login
Route::get('/', function() {
    return redirect()->route('login');
});

// Route test view (optional)
Route::get('/tes', function() {
    return view('tes');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Routes yang butuh login (middleware auth)
Route::middleware(['auth'])->group(function () {

    // Dashboard dengan data absensi
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Pegawai routes
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai');
    Route::prefix('pegawai')->group(function () {
        Route::post('/store', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::get('/{id}', [PegawaiController::class, 'show'])->name('pegawai.show');
        Route::put('/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
    });

    // Jam Kerja
    Route::get('/set_jam_kerja', [JamKerjaController::class, 'show'])->name('set_jam_kerja');
    Route::post('/save-jam-kerja', [JamKerjaController::class, 'store'])->name('save_jam_kerja');

    // Laporan Absensi via Controller (jika ingin tampil dari DB)
    Route::get('/laporan', [LaporanAbsensiController::class, 'index'])->name('laporan.index');
    // Halaman atur izin (bisa dibuat controller jika perlu logic)
    Route::get('/atur_izin', function () {
        return view('atur_izin');
    })->name('atur_izin');

    //laporan//
Route::get('/laporan', [LaporanAbsensiController::class, 'index'])->name('laporan.index');

    Route::post('/absensi', [AbsensiController::class, 'store']);


Route::get('/izin', [IzinController::class, 'index'])->name('izin.index');
Route::post('/izin', [IzinController::class, 'store'])->name('izin.store');


});
    