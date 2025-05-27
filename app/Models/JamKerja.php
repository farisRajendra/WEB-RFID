<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JamKerja extends Model
{
    use HasFactory;

    protected $table = 'jam_kerjas';

    protected $fillable = [
        'jam_masuk',
        'jam_keluar',
        'toleransi_masuk',
    ];

    protected $casts = [
        'jam_masuk' => 'string',   // tipe time di DB biasanya string
        'jam_keluar' => 'string',
        'toleransi_masuk' => 'integer', // asumsikan ini menit sebagai integer
    ];
}
