<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();
            
            // foreign key ke pegawais.id
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
            
            $table->date('tanggal');
            
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa', 'tidak hadir'])->default('hadir');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};