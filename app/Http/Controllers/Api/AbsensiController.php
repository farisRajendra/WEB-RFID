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

    public function store(Request $request)
    {
        Log::info("Memulai proses absensi RFID...");

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
            $jamMasukBatas = $jamMasukResmi->copy()->addMinutes(15);
            $batasWaktuMasukEkstrem = $jamMasukResmi->copy()->addHours(4);
            $jamDefault = $this->getDefaultTimestamp($tanggal);

            $absenHariIni = Absensi::where('pegawai_id', $pegawai->id)->whereDate('tanggal', $tanggal)->first();

            if (!$absenHariIni) {
                if ($wibTime->gte($batasWaktuMasukEkstrem)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Anda belum absen masuk hari ini. Tidak dapat absen di jam {$jamSekarang} tanpa absen masuk terlebih dahulu.",
                        'data' => [
                            'nama' => $pegawai->nama,
                            'jam_masuk_resmi' => $jamKerja->jam_masuk,
                            'jam_keluar_resmi' => $jamKerja->jam_keluar,
                            'waktu_sekarang' => $jamSekarang,
                            'keterangan' => 'Silakan hubungi admin untuk koreksi absensi'
                        ]
                    ], 400);
                }

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

            } else {
                $sudahPulang = $absenHariIni->jam_pulang && $absenHariIni->jam_pulang > $tanggal . ' 00:00:01';

                if (!$sudahPulang) {
                    $jamMasukTersimpan = Carbon::parse($absenHariIni->jam_masuk, 'Asia/Jakarta');
                    $selisihJam = $wibTime->diffInHours($jamMasukTersimpan);
                    $selisihMenit = $wibTime->diffInMinutes($jamMasukTersimpan);

                    if ($selisihJam < 4) {
                        return response()->json([
                            'success' => false,
                            'message' => "Anda sudah absen masuk pada {$jamMasukTersimpan->format('H:i')}. Minimal 4 jam untuk absen pulang (sisa: " . (4 - $selisihJam) . " jam)",
                            'data' => [
                                'nama' => $pegawai->nama,
                                'jam_masuk' => $jamMasukTersimpan->format('H:i'),
                                'sisa_jam' => 4 - $selisihJam,
                                'sisa_menit' => (4 * 60) - $selisihMenit % 60,
                                'waktu_sekarang' => $jamSekarang
                            ]
                        ], 400);
                    }

                    DB::beginTransaction();
                    $statusAkhir = $this->tentukanStatusAkhir($absenHariIni->status, $wibTime->gte($jamKeluarResmi));
                    $absenHariIni->update([
                        'jam_pulang' => $wibTime,
                        'status' => $statusAkhir,
                    ]);
                    $message = $this->getMessagePulang($statusAkhir, $wibTime->gte($jamKeluarResmi));
                    $jenisAbsensi = 'pulang';
                    $absen = $absenHariIni->fresh();
                    DB::commit();

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Anda sudah melakukan absensi masuk dan pulang hari ini'
                    ], 400);
                }
            }

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
                    'jam_masuk' => $absen->jam_masuk && $absen->jam_masuk > $tanggal . ' 00:00:01' ? Carbon::parse($absen->jam_masuk)->format('H:i:s') : null,
                    'jam_pulang' => $absen->jam_pulang && $absen->jam_pulang > $tanggal . ' 00:00:01' ? Carbon::parse($absen->jam_pulang)->format('H:i:s') : null,
                    'jam_kerja' => [
                        'masuk' => $jamKerja->jam_masuk,
                        'keluar' => $jamKerja->jam_keluar,
                    ]
                ]
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Terjadi kesalahan saat absensi: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses absensi. Coba lagi nanti.'
            ], 500);
        }
    }
}
