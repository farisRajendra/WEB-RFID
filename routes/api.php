<?php
use App\Http\Controllers\Api\AbsensiController;

Route::post('/absensi', [AbsensiController::class, 'store']);
