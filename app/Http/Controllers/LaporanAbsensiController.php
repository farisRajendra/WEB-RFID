<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Karyawan;

class LaporanAbsensiController extends Controller
{
   public function index()
{
    $absensi = Absensi::with('karyawan')->get();

$data = $absensi->map(function ($item, $index) {
    return [
        'no' => $index + 1,
        'nama' => $item->karyawan->nama ?? 'Tidak Diketahui',
        'id' => $item->karyawan->uid_rfid ?? '-',
        'tanggal' => $item->waktu_absen->format('Y-m-d'),
        'jamMasuk' => $item->waktu_absen->format('H:i:s'),
        'jamPulang' => null, // sementara kosong
        'status' => 'Hadir',
        'keterangan' => '-',
    ];
});

return view('laporan.index', ['absensiData' => $data]);


}
}
