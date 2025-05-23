<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pegawai;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absences';  // <-- ini supaya Laravel pakai tabel absences

    protected $fillable = [
    'pegawai_id',
    'rfid',
    'status',
    'created_at',
    'updated_at'
];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
