<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izin';
    protected $casts = [
    'tanggal' => 'date',
];

    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'keterangan',
    ];

    /**
     * Relasi ke model Pegawai (many to one)
     */
// Pada model Izin.php
public function pegawai()
{
    return $this->belongsTo(Pegawai::class);
}


}
