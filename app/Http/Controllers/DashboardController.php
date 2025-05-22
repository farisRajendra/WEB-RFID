<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Data dummy untuk dashboard
        $data = [
            'pegawai_masuk' => 85,
            'pegawai_tidak_masuk' => 15,
            'total_pegawai' => 100
        ];

        // Data dummy untuk grafik seminggu (Senin - Minggu)
        $chartData = [
            'labels' => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
            'pegawai_masuk' => [82, 88, 85, 90, 78, 45, 30],
            'pegawai_tidak_masuk' => [18, 12, 15, 10, 22, 55, 70]
        ];

        return view('dashboard', compact('data', 'chartData'));
    }
}