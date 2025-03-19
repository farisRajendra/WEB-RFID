<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rfid extends Model
{
    use HasFactory;
    
    protected $table = 'rfids';
    
    protected $primaryKey = 'rfid'; 
    
    public $incrementing = false; 
    
    protected $keyType = 'string'; 
    
    protected $fillable = [
        'rfid',
        'pegawai_id',
    ];
    
    /**
     * Relasi ke tabel Pegawai.
     */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }
}