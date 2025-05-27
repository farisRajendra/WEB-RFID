<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->id();

            // Foreign key ke pegawais.id, hapus data absence jika pegawai dihapus (cascade)
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');

            // Tanggal absen
            $table->date('tanggal');

            // Kolom jam masuk dan jam pulang, default waktu realtime saat record dibuat
            // Pastikan DB MySQL versi 5.6+ supaya CURRENT_TIMESTAMP di DATETIME support
            $table->dateTime('jam_masuk')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->dateTime('jam_pulang')->default(DB::raw('CURRENT_TIMESTAMP'));

            // Status kehadiran sesuai kebutuhan aplikasi
            $table->enum('status', ['hadir','terlambat','pulang_awal','pulang_duplikat','masuk','masuk_duplikat'])
                ->default('hadir');
            
            // Kolom created_at dan updated_at otomatis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absences');
    }
};
