<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
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

        // Ubah UID ke huruf besar untuk mencocokkan data case-sensitive
        $uid = strtoupper($request->uid_rfid);

        // Log UID yang diterima
        Log::info("UID dari ESP32: " . $uid);

        // Cari UID di tabel rfids secara case-sensitive
        $rfid = Rfid::whereRaw('BINARY `rfid` = ?', [$uid])->first();

        if (!$rfid) {
            // Jika tidak ditemukan
            Log::warning("RFID tidak ditemukan di tabel: " . $uid);

            return response()->json([
                'message' => 'Anda belum terdaftar'
            ], 404);
        }

      $absensi = Absensi::create([
        'pegawai_id' => $rfid->pegawai_id,
        'rfid' => $uid,
        'status' => 'hadir',
        'tanggal' => Carbon::now()->toDateString(),
    ]);

    return response()->json([
        'message' => 'Absensi berhasil',
        'data' => [
            'nama' => $rfid->pegawai->nama ?? 'Nama tidak tersedia',
            'jabatan' => $rfid->pegawai->jabatan ?? 'Jabatan tidak tersedia',
            'waktu' => now()->toDateTimeString()
        ]
    ], 201);
    }
}
