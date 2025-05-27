<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pegawai;  // Model pegawai
use App\Models\Absensi;  // Model absensi
use App\Models\Izin;     // Model izin
use Carbon\Carbon;

class IzinController extends Controller
{
   public function index(Request $request)
{
    $tanggal = $request->input('tanggal') ?? now()->toDateString();

    $pegawai = Pegawai::all();

    $pegawai_tidak_absen = $pegawai->filter(function ($p) use ($tanggal) {
        return !$p->absensi()->where('tanggal', $tanggal)->exists();
    });

    $izin = Izin::whereDate('tanggal', $tanggal)->with('pegawai')->get();

    return view('atur_izin', [
        'tanggal' => $tanggal,
        'pegawai_tidak_absen' => $pegawai_tidak_absen,
        'izin' => $izin
    ]);
}


    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'pegawai_id' => 'required|exists:pegawais,id', // PERBAIKAN: ganti pegawai menjadi pegawais
            'keterangan' => 'required|in:izin,sakit,dinas',
        ]);

        // Cek apakah pegawai sudah punya izin atau absensi pada tanggal tersebut
        $sudah_ada = Izin::where('pegawai_id', $request->pegawai_id)
                         ->whereDate('tanggal', $request->tanggal)
                         ->exists()
            || Absensi::where('pegawai_id', $request->pegawai_id)
                      ->whereDate('tanggal', $request->tanggal)
                      ->exists();

        if ($sudah_ada) {
            return redirect()->back()->withErrors(['pegawai_id' => 'Pegawai sudah memiliki absensi atau izin pada tanggal tersebut.']);
        }

        // Simpan data izin
        Izin::create([
            'tanggal' => $request->tanggal,
            'pegawai_id' => $request->pegawai_id,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('izin.index', ['tanggal' => $request->tanggal])
                         ->with('success', 'Data izin berhasil ditambahkan.');
    }
}