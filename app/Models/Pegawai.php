<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;
    
    protected $table = 'pegawais';

    protected $fillable = [
        'nama',
        'jabatan',
        'tanggal_lahir',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Relasi ke tabel RFID.
     */
    public function rfid()
    {
        return $this->hasOne(Rfid::class, 'pegawai_id');
    }
}