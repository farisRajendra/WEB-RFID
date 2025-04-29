<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\JamKerjaController;

Route::get('/', function() {
    return redirect()->route('login');
});
Route::get('/tes', function() {
    return view('tes');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai');
Route::prefix('pegawai')->group(function () {
    Route::post('/store', [PegawaiController::class, 'store'])->name('pegawai.store');
    Route::get('/{id}', [PegawaiController::class, 'show'])->name('pegawai.show'); 
    Route::put('/{id}', [PegawaiController::class, 'update'])->name('pegawai.update'); 
    Route::delete('/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy'); 
});

Route::get('/set_jam_kerja', [JamKerjaController::class, 'show'])->name('set_jam_kerja');
Route::post('/save-jam-kerja', [JamKerjaController::class, 'store'])->name('save_jam_kerja');


Route::get('/laporan_absen', function () {
    return view('laporan_absen');
})->name('laporan_absen');

Route::get('/atur_izin', function () {
    return view('atur_izin');
})->name('atur_izin');


});
