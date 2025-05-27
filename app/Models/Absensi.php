<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Pegawai;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absences'; // Pastikan ini cocok dengan nama tabel di database

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
    ];

    public $timestamps = true;

    protected $casts = [
        'tanggal' => 'date',
    ];

    /**
     * Relasi ke tabel pegawai
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}
