<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\Rfid;
use App\Models\Absensi;

Route::any('/absensi', function (Request $request) {
    $rfidId = $request->input('rfid_id');
    
    if (!$rfidId) {
        return response()->json([
            'success' => false,
            'message' => 'RFID ID diperlukan',
            'method' => $request->method()
        ]);
    }
    
    // Cek RFID di database (field 'rfid' bukan 'rfid_id')
    $rfidData = Rfid::where('rfid', $rfidId)->with('pegawai')->first();
    
    if (!$rfidData) {
        return response()->json([
            'success' => false,
            'message' => 'RFID belum terdaftar',
            'rfid_id' => $rfidId,
            'method' => $request->method()
        ]);
    }
    
    // Jika RFID terdaftar, buat record absensi
    $absensi = Absensi::create([
        'pegawai_id' => $rfidData->pegawai_id,
        'tanggal' => now()->toDateString(),
        'status' => 'hadir' // atau logic lain untuk masuk/keluar
    ]);
    
    return response()->json([
        'success' => true,
        'message' => 'Absensi berhasil dicatat',
        'rfid_id' => $rfidId,
        'pegawai' => $rfidData->pegawai,
        'absensi' => $absensi,
        'method' => $request->method()
    ]);
});