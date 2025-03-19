<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Rfid;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawai = Pegawai::with('rfid')->get();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($pegawai);
        }
        
        return view('pegawai', compact('pegawai'));
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'rfid' => 'nullable|string|max:255',
        ]);
        
        $pegawai = Pegawai::create([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
        ]);
        
        if (!empty($validated['rfid'])) {
            Rfid::create([
                'rfid' => $validated['rfid'],
                'pegawai_id' => $pegawai->id,
            ]);
        }
        
        return response()->json([
            'message' => 'Data pegawai berhasil disimpan',
            'data' => $pegawai->load('rfid')
        ]);
    }
    
    public function show($id)
    {
        $pegawai = Pegawai::with('rfid')->find($id);
        
        if (!$pegawai) {
            return response()->json([
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }
        
        return response()->json($pegawai);
    }
    
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::find($id);
        
        if (!$pegawai) {
            return response()->json([
                'message' => 'Pegawai tidak ditemukan'
            ], 404);
        }
        
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'rfid' => 'nullable|string|max:255',
        ]);
        
        // Update data pegawai
        $pegawai->update([
            'nama' => $validated['nama'],
            'jabatan' => $validated['jabatan'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
        ]);
        
        // Update atau buat data RFID
        if (!empty($validated['rfid'])) {
            // Check if RFID is already associated with another employee
            $existingRfid = Rfid::where('rfid', $validated['rfid'])
                ->where('pegawai_id', '!=', $pegawai->id)
                ->first();
                
            if ($existingRfid) {
                return response()->json([
                    'message' => 'RFID sudah digunakan oleh pegawai lain'
                ], 422);
            }
            
            // First, delete any existing RFID for this employee
            $pegawai->rfid()->delete();
            
            // Then create a new RFID record
            Rfid::create([
                'rfid' => $validated['rfid'],
                'pegawai_id' => $pegawai->id
            ]);
        } else {
            // Jika RFID kosong, hapus data RFID yang terkait
            $pegawai->rfid()->delete();
        }
        
        return response()->json([
            'message' => 'Data pegawai berhasil diperbarui',
            'data' => $pegawai->load('rfid')
        ]);
    }
    
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->rfid()->delete();
        
        $pegawai->delete();
        
        return response()->json([
            'message' => 'Data pegawai berhasil dihapus'
        ]);
    }
}