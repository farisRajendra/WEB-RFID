<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pegawai;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absences';

    protected $fillable = [
        'pegawai_id',
        'tanggal',        // Tambahkan ini
        'status',
        // Hapus 'rfid' karena sudah tidak ada
        // created_at dan updated_at tidak perlu ada di fillable
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}