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

            // foreign key ke pegawais.id, nullable dan on delete set null
            $table->foreignId('pegawai_id')->nullable()->constrained('pegawais')->onDelete('set null');

            // kolom rfid yang menjadi foreign key ke rfids.rfid
            $table->string('rfid');

            // pastikan rfid di rfids bertipe string dan unique
            $table->foreign('rfid')->references('rfid')->on('rfids')->onDelete('cascade');

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
