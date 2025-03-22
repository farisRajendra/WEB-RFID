<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JamKerja;

class JamKerjaController extends Controller
{
    // Menampilkan halaman pengaturan jam kerja
    public function show()
    {
        $jam_kerjas = JamKerja::latest()->first(); // Ambil data terbaru
        
        if (!$jam_kerjas) {
            $jam_kerjas = (object) [
                'jam_masuk' => '08:00:00',
                'jam_keluar' => '21:00:00'
            ];
        }
        
        return view('set_jam_kerja', compact('jam_kerjas'));
    }
    
    // Menyimpan atau memperbarui jam kerja
    public function store(Request $request)
    {
        $request->validate([
            'jam_masuk' => 'required|date_format:H:i',
            'jam_keluar' => 'required|date_format:H:i',
        ]);
        
        // Tambahkan ":00" untuk menyimpan dalam format H:i:s
        $jamMasuk = $request->jam_masuk . ":00";
        $jamKeluar = $request->jam_keluar . ":00";
        
        $jamKerja = JamKerja::first();
        
        if ($jamKerja) {
            $jamKerja->update([
                'jam_masuk' => $jamMasuk,
                'jam_keluar' => $jamKeluar
            ]);
        } else {
            $jamKerja = JamKerja::create([
                'jam_masuk' => $jamMasuk,
                'jam_keluar' => $jamKeluar
            ]);
        }
        
        return response()->json([
            'message' => 'Jam kerja berhasil disimpan!',
            'data' => $jamKerja
        ]);
    }
}