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

    public function store(Request $request)
    {
        Log::info("Memulai proses absensi RFID...");

        $request->validate([
            'rfid_id' => 'required|string|max:50',
        ]);

        $uid = strtoupper(trim($request->rfid_id));
        Log::info("Request absensi dengan RFID: {$uid}");

        try {
            // Validasi RFID dan Pegawai
            $rfid = Rfid::with('pegawai')
                ->whereRaw('BINARY rfid = ?', [$uid])
                ->first();

            if (!$rfid) {
                Log::warning("RFID tidak terdaftar: {$uid}");
                return response()->json([
                    'success' => false,
                    'message' => 'RFID belum terdaftar'
                ], 404);
            }

            $pegawai = $rfid->pegawai;
            if (!$pegawai) {
                Log::error("Pegawai tidak ditemukan untuk RFID: {$uid}");
                return response()->json([
                    'success' => false,
                    'message' => 'Data pegawai tidak ditemukan'
                ], 404);
            }

            Log::info("Pegawai ditemukan: {$pegawai->nama} (ID: {$pegawai->id})");

            // Setup waktu dan tanggal
            $wibTime = $this->getWibTime();
            $tanggal = $wibTime->format('Y-m-d');
            $wibDateTime = $wibTime->format('Y-m-d H:i:s');

            Log::info("Waktu Absensi (WIB): {$wibDateTime}");

            // Validasi jam kerja
            $jamKerja = JamKerja::first();

            if (!$jamKerja || !$jamKerja->jam_masuk || !$jamKerja->jam_keluar) {
                Log::error("Jam kerja belum diatur lengkap.");
                return response()->json([
                    'success' => false,
                    'message' => 'Jam kerja belum diatur lengkap, hubungi admin'
                ], 500);
            }

            Log::info("Jam kerja ditemukan: masuk {$jamKerja->jam_masuk}, keluar {$jamKerja->jam_keluar}");

            // Setup jam kerja dengan timezone yang benar
            $jamMasukResmi = Carbon::createFromFormat('H:i:s', $jamKerja->jam_masuk, 'Asia/Jakarta');
            $jamKeluarResmi = Carbon::createFromFormat('H:i:s', $jamKerja->jam_keluar, 'Asia/Jakarta');
            $toleransiMenit = 15;
            $jamMasukBatas = $jamMasukResmi->copy()->addMinutes($toleransiMenit);

            // STEP 1: Cek record absensi hari ini
            $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)
                ->whereDate('tanggal', $tanggal)
                ->first();

            DB::beginTransaction();

            if (!$absenHariIni) {
                // STEP 2A: Belum ada record - ABSENSI MASUK
                Log::info("Tidak ada record absensi hari ini, memproses absensi masuk");
                
                // Validasi waktu masuk
                $statusMasuk = $wibTime->gt($jamMasukBatas) ? 'terlambat' : 'hadir';

                $absen = Absensi::create([
                    'pegawai_id' => $pegawai->id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $wibTime,
                    'jam_pulang' => null, // NULL untuk menandakan belum pulang
                    'status' => $statusMasuk,
                ]);

                $message = $statusMasuk === 'terlambat' ? 'Absensi masuk terlambat' : 'Absensi masuk berhasil';
                $jenisAbsensi = 'masuk';

                Log::info("Record absensi masuk disimpan. Jam: {$wibDateTime}, Status: {$statusMasuk}");

            } else {
                // STEP 2B: Sudah ada record - Cek apakah sudah pulang
                if (is_null($absenHariIni->jam_pulang)) {
                    // STEP 3: Record ada, belum pulang - ABSENSI PULANG
                    Log::info("Record ditemukan, memproses absensi pulang");

                    $statusMasukExisting = $absenHariIni->status;
                    
                    // Validasi waktu pulang
                    $pulangNormal = $wibTime->gte($jamKeluarResmi);
                    
                    // Tentukan status akhir berdasarkan tabel keputusan
                    $statusAkhir = $this->tentukanStatusAkhir($statusMasukExisting, $pulangNormal);

                    // Update record yang sama
                    $absenHariIni->update([
                        'jam_pulang' => $wibTime,
                        'status' => $statusAkhir,
                    ]);

                    $message = $this->getMessagePulang($statusAkhir, $pulangNormal);
                    $jenisAbsensi = 'pulang';
                    $absen = $absenHariIni->fresh(); // Refresh data

                    Log::info("Record absensi pulang diupdate. Jam: {$wibDateTime}, Status: {$statusAkhir}");

                } else {
                    // STEP 2B-2: Sudah lengkap (masuk dan pulang)
                    Log::warning("Absensi sudah lengkap untuk hari ini");
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan absensi masuk dan pulang hari ini'
                    ], 400);
                }
            }

            DB::commit();
            Log::info("Absensi selesai disimpan ke database.");

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
                    'jam_masuk' => $absen->jam_masuk ? $absen->jam_masuk->format('H:i:s') : null,
                    'jam_pulang' => $absen->jam_pulang ? $absen->jam_pulang->format('H:i:s') : null,
                ]
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Error dalam proses absensi: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan dalam sistem'
            ], 500);
        }
    }

    /**
     * Tentukan status akhir berdasarkan tabel keputusan
     * 
     * @param string $statusMasuk
     * @param bool $pulangNormal
     * @return string
     */
    private function tentukanStatusAkhir($statusMasuk, $pulangNormal)
    {
        // Tabel keputusan status
        if ($statusMasuk === 'hadir') {
            return $pulangNormal ? 'hadir' : 'pulang_awal';
        }
        
        if ($statusMasuk === 'terlambat') {
            return $pulangNormal ? 'terlambat' : 'terlambat_dan_pulang_awal';  
        }

        // Fallback (seharusnya tidak terjadi)
        return $statusMasuk;
    }

    /**
     * Generate message untuk absensi pulang
     * 
     * @param string $statusAkhir
     * @param bool $pulangNormal  
     * @return string
     */
    private function getMessagePulang($statusAkhir, $pulangNormal)
    {
        switch ($statusAkhir) {
            case 'hadir':
                return 'Absensi pulang berhasil';
            case 'pulang_awal':
                return 'Absensi pulang awal berhasil';
            case 'terlambat':
                return 'Absensi pulang berhasil (masuk terlambat)';
            case 'terlambat_dan_pulang_awal':
                return 'Absensi pulang awal berhasil (masuk terlambat)';
            default:
                return 'Absensi pulang berhasil';
        }
    }
}