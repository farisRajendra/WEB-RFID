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
                $allData->push([
                    'id' => $absensi->pegawai->rfid->rfid ?? '-',
                    'nama' => $absensi->pegawai->nama ?? '-',
                    'tanggal' => $tanggal,
                    'jamMasuk' => $absensi->jam_masuk ?? '-',
                    'jamPulang' => $absensi->jam_pulang ?? '-',
                    'status' => $absensi->status ?? 'Hadir',
                    'keterangan' => '-'
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
}