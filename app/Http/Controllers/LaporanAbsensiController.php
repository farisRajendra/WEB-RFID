<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use Carbon\Carbon;
use App\Models\Izin;

class LaporanAbsensiController extends Controller
{
    public function index()
    {
        // Ambil data untuk 1 bulan terakhir dan 1 bulan ke depan
        $startDate = Carbon::now()->subMonth()->format('Y-m-d');
        $endDate = Carbon::now()->addMonth()->format('Y-m-d');
        
        // Generate array tanggal dalam range
        $dateRange = [];
        $current = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        while ($current->lte($end)) {
            $dateRange[] = $current->format('Y-m-d');
            $current->addDay();
        }
        
        // Ambil semua pegawai
        $semuaPegawai = Pegawai::with('rfid')->get();
        
        // Ambil data absensi dalam range
        $absensiRange = Absensi::with('pegawai.rfid')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                return $item->tanggal->format('Y-m-d');
            });
            
        // Ambil data izin dalam range
        $izinRange = Izin::with('pegawai.rfid')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy(function($item) {
                return $item->tanggal->format('Y-m-d');
            });
        
        $allData = collect();
        
        // Loop untuk setiap tanggal dalam range
        foreach ($dateRange as $tanggal) {
            $absensiHari = $absensiRange->get($tanggal, collect());
            $izinHari = $izinRange->get($tanggal, collect());
            
            // Buat array ID yang sudah hadir/izin
            $hadirIds = $absensiHari->pluck('pegawai.rfid.rfid')->filter()->all();
            $izinIds = $izinHari->pluck('pegawai.rfid.rfid')->filter()->all();
            $sudahAdaIds = array_merge($hadirIds, $izinIds);
            
            // Tambahkan data absensi
            foreach ($absensiHari as $absensi) {
                $status = $absensi->status ?? 'hadir';
                $keterangan = $this->buatKeterangan($absensi, $status);
                
                $allData->push([
                    'id' => $absensi->pegawai->rfid->rfid ?? '-',
                    'nama' => $absensi->pegawai->nama ?? '-',
                    'tanggal' => $tanggal,
                    'jamMasuk' => $absensi->jam_masuk ?? '-',
                    'jamPulang' => $absensi->jam_pulang ?? '-',
                    'status' => ucfirst($status),
                    'keterangan' => $keterangan
                ]);
            }
            
            // Tambahkan data izin
            foreach ($izinHari as $izin) {
                $allData->push([
                    'id' => $izin->pegawai->rfid->rfid ?? '-',
                    'nama' => $izin->pegawai->nama ?? '-',
                    'tanggal' => $tanggal,
                    'jamMasuk' => '-',
                    'jamPulang' => '-',
                    'status' => 'IZIN',
                    'keterangan' => $izin->keterangan ?? '-'
                ]);
            }
            
            // Tambahkan Alpha untuk pegawai yang tidak hadir dan tidak izin
            foreach ($semuaPegawai as $pegawai) {
                $rfid = $pegawai->rfid->rfid ?? null;
                if ($rfid && !in_array($rfid, $sudahAdaIds)) {
                    $allData->push([
                        'id' => $rfid,
                        'nama' => $pegawai->nama,
                        'tanggal' => $tanggal,
                        'jamMasuk' => '-',
                        'jamPulang' => '-',
                        'status' => 'Alpha',
                        'keterangan' => '-'
                    ]);
                }
            }
        }
        
        return view('laporan_absen', ['absensiData' => $allData]);
    }
    
    /**
     * Buat keterangan berdasarkan status dan durasi kerja
     */
    private function buatKeterangan($absensi, $status)
    {
        // Debug: Log data yang masuk
        \Log::info('buatKeterangan called', [
            'status' => $status,
            'jam_masuk' => $absensi->jam_masuk ?? null,
            'jam_pulang' => $absensi->jam_pulang ?? null,
            'absensi_status' => $absensi->status ?? null
        ]);
        
        // Daftar status yang perlu ditampilkan durasi kerjanya
        $statusDenganDurasi = ['hadir', 'terlambat', 'pulang_awal'];
        
        // Cek apakah status termasuk yang perlu dihitung durasinya
        if (in_array(strtolower($status), $statusDenganDurasi)) {
            $durasi = $this->hitungDurasi($absensi->jam_masuk, $absensi->jam_pulang);
            
            \Log::info('buatKeterangan: Durasi calculated', [
                'durasi' => $durasi,
                'status' => $status
            ]);
            
            if ($durasi !== '-') {
                // Buat keterangan berdasarkan status
                switch (strtolower($status)) {
                    case 'hadir':
                        $result = 'Durasi kerja: ' . $durasi;
                        break;
                    case 'terlambat':
                        $result = 'Terlambat - Durasi kerja: ' . $durasi;
                        break;
                    case 'pulang_awal':
                        $result = 'Pulang awal - Durasi kerja: ' . $durasi;
                        break;
                    default:
                        $result = 'Durasi kerja: ' . $durasi;
                }
                
                \Log::info('buatKeterangan: Result with duration', ['result' => $result]);
                return $result;
            }
        }
        
        // Keterangan default untuk status lainnya
        $defaultResult = '';
        switch (strtolower($status)) {
            case 'terlambat':
                $defaultResult = 'Datang terlambat';
                break;
            case 'pulang_awal':
                $defaultResult = 'Pulang lebih awal';
                break;
            case 'hadir':
                $defaultResult = 'Hadir tepat waktu';
                break;
            default:
                $defaultResult = '-';
        }
        
        \Log::info('buatKeterangan: Default result', ['result' => $defaultResult]);
        return $defaultResult;
    }
    
    /**
     * Hitung durasi kerja dalam format jam:menit
     * PERBAIKAN: Menggunakan Carbon diff() method yang lebih akurat
     */
    private function hitungDurasi($jamMasuk, $jamPulang)
    {
        // Debug: Log input data
        \Log::info('hitungDurasi called', [
            'jam_masuk' => $jamMasuk,
            'jam_pulang' => $jamPulang,
            'jam_masuk_type' => gettype($jamMasuk),
            'jam_pulang_type' => gettype($jamPulang)
        ]);
        
        if (!$jamMasuk || !$jamPulang || $jamMasuk == '-' || $jamPulang == '-') {
            \Log::info('hitungDurasi: Data tidak lengkap');
            return '-';
        }
        
        try {
            // Parse jam masuk dan pulang menggunakan Carbon
            $checkIn = \Carbon\Carbon::parse($jamMasuk);
            $checkOut = \Carbon\Carbon::parse($jamPulang);
            
            \Log::info('hitungDurasi: Parsed times', [
                'check_in' => $checkIn->format('Y-m-d H:i:s'),
                'check_out' => $checkOut->format('Y-m-d H:i:s')
            ]);
            
            // Cek apakah jam pulang adalah default timestamp (00:00:01 atau 00:00:00)
            if ($checkOut->format('H:i:s') === '00:00:01' || $checkOut->format('H:i:s') === '00:00:00') {
                \Log::info('hitungDurasi: Jam pulang default timestamp');
                return '-'; // Belum absen pulang
            }
            
            // Jika jam pulang lebih kecil dari jam masuk (lintas hari)
            if ($checkOut->lt($checkIn)) {
                $checkOut->addDay();
                \Log::info('hitungDurasi: Lintas hari, jam pulang disesuaikan');
            }
            
            // Hitung durasi menggunakan diff()
            $duration = $checkIn->diff($checkOut);
            $hours = $duration->h;
            $minutes = $duration->i;
            
            // Tambahkan hari jika ada
            if ($duration->d > 0) {
                $hours += ($duration->d * 24);
            }
            
            \Log::info('hitungDurasi: Duration calculated', [
                'days' => $duration->d,
                'hours' => $hours,
                'minutes' => $minutes,
                'total_hours' => $hours
            ]);
            
            // Format output
            if ($hours > 0 && $minutes > 0) {
                $result = sprintf('%d jam %d menit', $hours, $minutes);
            } elseif ($hours > 0) {
                $result = sprintf('%d jam', $hours);
            } elseif ($minutes > 0) {
                $result = sprintf('%d menit', $minutes);
            } else {
                // Jika benar-benar 0, cek total detik
                $totalSeconds = $checkIn->diffInSeconds($checkOut);
                if ($totalSeconds > 0) {
                    $result = '1 menit'; // Minimal 1 menit untuk durasi yang ada
                } else {
                    $result = '0 menit';
                }
            }
            
            \Log::info('hitungDurasi: Final result', ['result' => $result]);
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('hitungDurasi: Exception', [
                'message' => $e->getMessage(),
                'jam_masuk' => $jamMasuk,
                'jam_pulang' => $jamPulang
            ]);
            return '-';
        }
    }
}