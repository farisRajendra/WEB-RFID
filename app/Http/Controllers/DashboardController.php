<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Pegawai;
use App\Models\Izin;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Ambil semua pegawai
        $semuaPegawai = Pegawai::with('rfid')->get();
        $totalPegawai = $semuaPegawai->count();

        // Tanggal hari ini
        $today = Carbon::now()->format('Y-m-d');

        // Ambil absensi hari ini
        $absensiHariIni = Absensi::with('pegawai.rfid')
            ->whereDate('tanggal', $today)
            ->get();

        // Ambil izin hari ini
        $izinHariIni = Izin::with('pegawai.rfid')
            ->whereDate('tanggal', $today)
            ->get();

        // Buat array RFID yang sudah absen dan izin
        $rfidAbsen = $absensiHariIni->pluck('pegawai.rfid.rfid')->filter()->toArray();
        $rfidIzin = $izinHariIni->pluck('pegawai.rfid.rfid')->filter()->toArray();
        $rfidSudahAda = array_merge($rfidAbsen, $rfidIzin);

        // Hitung pegawai yang benar-benar masuk (hanya absensi)
        $pegawaiMasuk = count($rfidAbsen);

        // Hitung pegawai Alpha (yang tidak ada di absen dan tidak izin)
        $pegawaiAlpha = 0;
        foreach ($semuaPegawai as $pegawai) {
            $rfid = $pegawai->rfid->rfid ?? null;
            if ($rfid && !in_array($rfid, $rfidSudahAda)) {
                $pegawaiAlpha++;
            }
        }

        // Hitung pegawai yang tidak masuk (Alpha + Izin)
        $pegawaiTidakMasuk = $pegawaiAlpha + count($rfidIzin);

        // Data untuk dashboard stats
        $data = [
            'pegawai_masuk' => $pegawaiMasuk,
            'pegawai_tidak_masuk' => $pegawaiTidakMasuk,
            'total_pegawai' => $totalPegawai
        ];

        // Data untuk grafik Senin–Sabtu
        $chartData = $this->getWeeklyChartData();

        return view('dashboard', compact('data', 'chartData'));
    }

    private function getWeeklyChartData()
    {
        $labels = [];
        $pegawaiMasukData = [];
        $pegawaiTidakMasukData = [];

        // Nama hari dalam bahasa Indonesia
        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        // Cari Senin dari minggu ini
        $today = Carbon::now();
        $monday = $today->copy()->startOfWeek(Carbon::MONDAY);
        
        // Jika hari ini Minggu, ambil Senin minggu lalu
        if ($today->dayOfWeek == Carbon::SUNDAY) {
            $monday = $monday->subWeek();
        }

        $dates = [];
        
        // Ambil tanggal Senin sampai Sabtu
        for ($i = 0; $i < 6; $i++) {
            $date = $monday->copy()->addDays($i);
            $dates[] = $date;
        }

        // Ambil semua pegawai hanya sekali
        $allPegawai = Pegawai::with('rfid')->get();

        foreach ($dates as $date) {
            $tanggal = $date->format('Y-m-d');
            $dayName = $dayMap[$date->format('l')];

            $labels[] = $dayName;

            $absensiHari = Absensi::with('pegawai.rfid')->whereDate('tanggal', $tanggal)->get();
            $izinHari = Izin::with('pegawai.rfid')->whereDate('tanggal', $tanggal)->get();

            $rfidAbsen = $absensiHari->pluck('pegawai.rfid.rfid')->filter()->toArray();
            $rfidIzin = $izinHari->pluck('pegawai.rfid.rfid')->filter()->toArray();
            $rfidSudahAda = array_merge($rfidAbsen, $rfidIzin);

            $pegawaiMasuk = count($rfidAbsen);

            $pegawaiAlpha = 0;
            foreach ($allPegawai as $pegawai) {
                $rfid = $pegawai->rfid->rfid ?? null;
                if ($rfid && !in_array($rfid, $rfidSudahAda)) {
                    $pegawaiAlpha++;
                }
            }

            $pegawaiTidakMasuk = $pegawaiAlpha + count($rfidIzin);

            $pegawaiMasukData[] = $pegawaiMasuk;
            $pegawaiTidakMasukData[] = $pegawaiTidakMasuk;
        }

        return [
            'labels' => $labels,
            'pegawai_masuk' => $pegawaiMasukData,
            'pegawai_tidak_masuk' => $pegawaiTidakMasukData
        ];
    }
}