<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Rfid;
use App\Models\JamKerja;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    private function getWibTime()
    {
        return Carbon::now('Asia/Jakarta');
    }

    private function getDefaultTimestamp($tanggal)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $tanggal . ' 00:00:01', 'Asia/Jakarta');
    }

    private function tentukanStatusAkhir($statusMasuk, $pulangNormal)
    {
        if ($statusMasuk === 'terlambat') {
            return $pulangNormal ? 'terlambat' : 'pulang_awal';
        } elseif ($statusMasuk === 'hadir') {
            return $pulangNormal ? 'hadir' : 'pulang_awal';
        } else {
            return $statusMasuk;
        }
    }

    private function getMessagePulang($statusAkhir, $pulangNormal)
    {
        if (!$pulangNormal) {
            return "Absensi pulang lebih awal dari jadwal. Status: {$statusAkhir}";
        }

        return "Absensi pulang berhasil. Status: {$statusAkhir}";
    }

    private function hitungJumlahTap($pegawaiId, $tanggal)
    {
        // Ambil data absensi hari ini
        $absenHariIni = Absensi::where('pegawai_id', $pegawaiId)
            ->whereDate('tanggal', $tanggal)
            ->first();

        if (!$absenHariIni) {
            return 0; // TAP 1: Belum pernah tap
        }

        // Cek apakah sudah ada jam pulang yang valid (bukan default 00:00:01)
        $jamPulangValid = $absenHariIni->jam_pulang && 
                         Carbon::parse($absenHariIni->jam_pulang)->format('H:i:s') != '00:00:01';

        if ($jamPulangValid) {
            return 3; // TAP 4+: Sudah lengkap
        }

        // Gunakan simple cache counter untuk tracking tap setelah masuk
        $cacheKey = "tap_sequence_{$pegawaiId}_{$tanggal}";
        $tapSequence = cache($cacheKey, 1); // Default 1 setelah masuk
        
        Log::info("Cache key: {$cacheKey}, Current sequence: {$tapSequence}");
        
        // Increment counter untuk tap berikutnya
        cache([$cacheKey => $tapSequence + 1], 1440); // Cache 24 jam
        
        return $tapSequence;
    }

    public function store(Request $request)
    {
        Log::info("Memulai proses absensi RFID dengan sistem toggle...");

        $request->validate([
            'rfid_id' => 'required|string|max:50',
        ]);

        $uid = strtoupper(trim($request->rfid_id));
        Log::info("Request absensi dengan RFID: {$uid}");

        try {
            $rfid = Rfid::with('pegawai')->whereRaw('BINARY rfid = ?', [$uid])->first();

            if (!$rfid) {
                return response()->json(['success' => false, 'message' => 'RFID belum terdaftar'], 404);
            }

            $pegawai = $rfid->pegawai;
            if (!$pegawai) {
                return response()->json(['success' => false, 'message' => 'Data pegawai tidak ditemukan'], 404);
            }

            $wibTime = $this->getWibTime();
            $tanggal = $wibTime->format('Y-m-d');
            $wibDateTime = $wibTime->format('Y-m-d H:i:s');
            $jamSekarang = $wibTime->format('H:i:s');

            $jamKerja = JamKerja::first();
            if (!$jamKerja || !$jamKerja->jam_masuk || !$jamKerja->jam_keluar) {
                return response()->json(['success' => false, 'message' => 'Jam kerja belum diatur lengkap, hubungi admin'], 500);
            }

            $jamMasukResmi = Carbon::createFromFormat('H:i:s', $jamKerja->jam_masuk, 'Asia/Jakarta');
            $jamKeluarResmi = Carbon::createFromFormat('H:i:s', $jamKerja->jam_keluar, 'Asia/Jakarta');
            $jamMasukBatas = $jamMasukResmi->copy()->addMinutes(2);
            $jamDefault = $this->getDefaultTimestamp($tanggal);

            // SISTEM TOGGLE: Hitung jumlah tap hari ini
            $jumlahTap = $this->hitungJumlahTap($pegawai->id, $tanggal);
            Log::info("Pegawai {$pegawai->nama} - Jumlah tap hari ini: {$jumlahTap}");
            
            // Debug: Cek kondisi data absensi
            $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', $tanggal)->first();
            if ($absenHariIni) {
                $jamPulangFormatted = $absenHariIni->jam_pulang ? Carbon::parse($absenHariIni->jam_pulang)->format('H:i:s') : 'NULL';
                Log::info("Data absensi: jam_masuk={$absenHariIni->jam_masuk}, jam_pulang={$jamPulangFormatted}");
            }

            // TAP 1: ABSEN MASUK (Pertama kali tap)
            if ($jumlahTap == 0) {
                Log::info("TAP 1: Proses absen masuk untuk {$pegawai->nama}");

                // Reset cache counter untuk pegawai ini
                $cacheKey = "tap_sequence_{$pegawai->id}_{$tanggal}";
                cache()->forget($cacheKey);

                // Validasi waktu masuk
                if ($wibTime->gte($jamKeluarResmi)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Tidak dapat absen masuk di jam {$jamSekarang}. Jam kerja telah berakhir pada {$jamKerja->jam_keluar}.",
                        'data' => [
                            'nama' => $pegawai->nama,
                            'jam_masuk_resmi' => $jamKerja->jam_masuk,
                            'jam_keluar_resmi' => $jamKerja->jam_keluar,
                            'waktu_sekarang' => $jamSekarang,
                            'keterangan' => 'Silakan hubungi admin untuk koreksi absensi'
                        ]
                    ], 400);
                }

                $statusMasuk = $wibTime->gt($jamMasukBatas) ? 'terlambat' : 'hadir';

                DB::beginTransaction();
                $absen = Absensi::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $wibTime,
                    'jam_pulang' => $jamDefault,
                    'status' => $statusMasuk,
                ]);
                DB::commit();

                $message = $statusMasuk === 'terlambat' ? 'Absensi masuk terlambat' : 'Absensi masuk berhasil';
                $jenisAbsensi = 'masuk';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'nama' => $pegawai->nama,
                        'jabatan' => $pegawai->jabatan ?? '-',
                        'tanggal' => $tanggal,
                        'jam' => $wibDateTime,
                        'status' => $absen->status,
                        'jenis' => $jenisAbsensi,
                        'jam_masuk' => Carbon::parse($absen->jam_masuk)->format('H:i:s'),
                        'jam_pulang' => null,
                        'jam_kerja' => [
                            'masuk' => $jamKerja->jam_masuk,
                            'keluar' => $jamKerja->jam_keluar,
                        ],
                        'keterangan' => 'Tap sekali lagi untuk absen pulang'
                    ]
                ], 200);
            }

            // TAP 2: KONFIRMASI (Tidak simpan ke database)
            elseif ($jumlahTap == 1) {
                Log::info("TAP 2: Konfirmasi absen masuk untuk {$pegawai->nama}");

                $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $jamMasukTersimpan = Carbon::parse($absenHariIni->jam_masuk, 'Asia/Jakarta');

                return response()->json([
                    'success' => true,
                    'message' => '✅ Anda sudah absen masuk hari ini',
                    'data' => [
                        'nama' => $pegawai->nama,
                        'jabatan' => $pegawai->jabatan ?? '-',
                        'tanggal' => $tanggal,
                        'jam' => $wibDateTime,
                        'status' => $absenHariIni->status,
                        'jenis' => 'konfirmasi',
                        'jam_masuk' => $jamMasukTersimpan->format('H:i:s'),
                        'jam_pulang' => null,
                        'jam_kerja' => [
                            'masuk' => $jamKerja->jam_masuk,
                            'keluar' => $jamKerja->jam_keluar,
                        ],
                        'keterangan' => '🕐 Waktu masuk: ' . $jamMasukTersimpan->format('H:i') . ' WIB',
                        'info_tambahan' => '💡 Tap sekali lagi untuk absen pulang'
                    ]
                ], 200);
            }

            // TAP 3: ABSEN PULANG (Langsung simpan)
            elseif ($jumlahTap == 2) {
                Log::info("TAP 3: Proses absen pulang untuk {$pegawai->nama}");

                $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $jamMasukTersimpan = Carbon::parse($absenHariIni->jam_masuk, 'Asia/Jakarta');
                
                Log::info("DEBUG TAP 3 - Jam masuk: {$jamMasukTersimpan->format('Y-m-d H:i:s')}");
                Log::info("DEBUG TAP 3 - Jam sekarang: {$wibTime->format('Y-m-d H:i:s')}");

                try {
                    DB::beginTransaction();
                    
                    $statusAkhir = $this->tentukanStatusAkhir($absenHariIni->status, $wibTime->gte($jamKeluarResmi));
                    Log::info("DEBUG TAP 3 - Status akhir: {$statusAkhir}");
                    
                    $updateResult = $absenHariIni->update([
                        'jam_pulang' => $wibTime,
                        'status' => $statusAkhir,
                    ]);
                    
                    Log::info("DEBUG TAP 3 - Update result: " . ($updateResult ? 'SUCCESS' : 'FAILED'));
                    
                    if (!$updateResult) {
                        Log::error("DEBUG TAP 3 - DATABASE UPDATE FAILED!");
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Gagal update database'
                        ], 500);
                    }
                    
                    $message = $this->getMessagePulang($statusAkhir, $wibTime->gte($jamKeluarResmi));
                    $jenisAbsensi = 'pulang';
                    $absen = $absenHariIni->fresh();
                    
                    Log::info("DEBUG TAP 3 - Data setelah update: jam_pulang=" . Carbon::parse($absen->jam_pulang)->format('Y-m-d H:i:s'));
                    
                    DB::commit();
                    Log::info("DEBUG TAP 3 - Database transaction committed successfully");

                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'data' => [
                            'nama' => $pegawai->nama,
                            'jabatan' => $pegawai->jabatan ?? '-',
                            'tanggal' => $tanggal,
                            'jam' => $wibDateTime,
                            'status' => $absen->status,
                            'jenis' => $jenisAbsensi,
                            'jam_masuk' => Carbon::parse($absen->jam_masuk)->format('H:i:s'),
                            'jam_pulang' => Carbon::parse($absen->jam_pulang)->format('H:i:s'),
                            'jam_kerja' => [
                                'masuk' => $jamKerja->jam_masuk,
                                'keluar' => $jamKerja->jam_keluar,
                            ]
                        ]
                    ], 200);
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error("DEBUG TAP 3 - Exception during database update: " . $e->getMessage());
                    Log::error("DEBUG TAP 3 - Exception trace: " . $e->getTraceAsString());
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Error saat update database: ' . $e->getMessage()
                    ], 500);
                }
            }

            // TAP 4+: ABSENSI SUDAH LENGKAP (Abaikan)
            else {
                Log::info("TAP 4+: Absensi sudah lengkap untuk {$pegawai->nama}");

                $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();

                $jamMasukTersimpan = Carbon::parse($absenHariIni->jam_masuk, 'Asia/Jakarta');
                $jamPulangTersimpan = Carbon::parse($absenHariIni->jam_pulang, 'Asia/Jakarta');

                return response()->json([
                    'success' => true,
                    'message' => '✅ Absensi Anda hari ini sudah lengkap',
                    'data' => [
                        'nama' => $pegawai->nama,
                        'jabatan' => $pegawai->jabatan ?? '-',
                        'tanggal' => $tanggal,
                        'jam' => $wibDateTime,
                        'status' => $absenHariIni->status,
                        'jenis' => 'lengkap',
                        'jam_masuk' => $jamMasukTersimpan->format('H:i:s'),
                        'jam_pulang' => $jamPulangTersimpan->format('H:i:s'),
                        'jam_kerja' => [
                            'masuk' => $jamKerja->jam_masuk,
                            'keluar' => $jamKerja->jam_keluar,
                        ],
                        'ringkasan' => "🕐 Masuk: {$jamMasukTersimpan->format('H:i')} | Pulang: {$jamPulangTersimpan->format('H:i')}"
                    ]
                ], 200);
            }

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan saat absensi: " . $e->getMessage());
            Log::error("Error trace: " . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absensi. Coba lagi nanti.'
            ], 500);
        }
    }
}