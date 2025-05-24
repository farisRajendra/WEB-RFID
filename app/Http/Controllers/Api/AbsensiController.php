<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;  // Tetap pakai Absensi
use App\Models\Rfid;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AbsensiController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input dari ESP32
        $request->validate([
            'uid_rfid' => 'required|string',
        ]);

        $uid = strtoupper($request->uid_rfid);
        Log::info("UID dari ESP32: " . $uid);

        // Cari UID dengan eager loading
        $rfid = Rfid::with('pegawai')->whereRaw('BINARY `rfid` = ?', [$uid])->first();

        if (!$rfid) {
            Log::warning("RFID tidak ditemukan: " . $uid);
            return response()->json([
                'message' => 'Anda belum terdaftar'
            ], 404);
        }

        // Cek duplikasi absen hari ini
        $tanggalHariIni = Carbon::now()->toDateString();
        $sudahAbsen = Absensi::where('pegawai_id', $rfid->pegawai_id)
                             ->where('tanggal', $tanggalHariIni)
                             ->exists();

        if ($sudahAbsen) {
            return response()->json([
                'message' => 'Anda sudah absen hari ini'
            ], 409);
        }

        // Buat absensi baru (tanpa kolom rfid)
        $absensi = Absensi::create([
            'pegawai_id' => $rfid->pegawai_id,
            'tanggal' => $tanggalHariIni,
            'status' => 'hadir',
        ]);

        return response()->json([
            'message' => 'Absensi berhasil',
            'data' => [
                'nama' => $rfid->pegawai->nama,
                'jabatan' => $rfid->pegawai->jabatan,
                'waktu' => now()->toDateTimeString()
            ]
        ], 201);
    }
}