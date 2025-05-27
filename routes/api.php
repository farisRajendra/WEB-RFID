<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsensiController;

Route::post('/absensi', [AbsensiController::class, 'store']);


// use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;
// use App\Models\Rfid;
// use App\Models\Absensi;
// use Carbon\Carbon;

// Route::get('/debug-timezone', [App\Http\Controllers\Api\DebugController::class, 'debugTimezone']);

// Route::post('/absensi', function (Request $request) {
//     $rfidId = $request->input('rfid_id');

//     if (!$rfidId) {
//         return response()->json([
//             'success' => false,
//             'message' => 'RFID ID diperlukan',
//         ], 400);
//     }

//     $rfidData = Rfid::where('rfid', $rfidId)->with('pegawai')->first();

//     if (!$rfidData) {
//         return response()->json([
//             'success' => false,
//             'message' => 'RFID belum terdaftar',
//             'rfid_id' => $rfidId,
//         ], 404);
//     }

//     $pegawaiId = $rfidData->pegawai_id;
//     $tanggalHariIni = now()->toDateString();
//     $jamSekarang = now()->format('H:i:s');

//     // Cek apakah pegawai sudah absen hari ini
//     $absensiHariIni = Absensi::where('pegawai_id', $pegawaiId)
//         ->where('tanggal', $tanggalHariIni)
//         ->first();

//     if ($absensiHariIni) {
//         // Jika sudah absen masuk, update jam pulang
//         if (!$absensiHariIni->jam_pulang) {
//             $absensiHariIni->jam_pulang = $jamSekarang;
//             $absensiHariIni->save();

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Jam pulang berhasil dicatat',
//                 'rfid_id' => $rfidId,
//                 'pegawai' => $rfidData->pegawai,
//                 'absensi' => $absensiHariIni,
//             ], 200);
//         } else {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Pegawai sudah absen masuk dan pulang hari ini',
//                 'rfid_id' => $rfidId,
//                 'pegawai' => $rfidData->pegawai,
//                 'absensi' => $absensiHariIni,
//             ], 200);
//         }
//     }

//     // Belum absen hari ini → buat record baru sebagai jam masuk
//     $absensiBaru = Absensi::create([
//         'pegawai_id' => $pegawaiId,
//         'tanggal' => $tanggalHariIni,
//         'jam_masuk' => $jamSekarang,
//         'jam_pulang' => null,
//         'status' => 'hadir',
//     ]);

//     return response()->json([
//         'success' => true,
//         'message' => 'Absensi masuk berhasil dicatat',
//         'rfid_id' => $rfidId,
//         'pegawai' => $rfidData->pegawai,
//         'absensi' => $absensiBaru,
//     ], 201);
// }); 
