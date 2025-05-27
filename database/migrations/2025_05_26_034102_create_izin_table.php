<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/xxxx_xx_xx_create_izin_table.php
public function up()
{
    Schema::create('izin', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('pegawai_id');
        $table->date('tanggal');
        $table->enum('keterangan', ['izin', 'sakit', 'dinas']);
        $table->timestamps();

        $table->foreign('pegawai_id')->references('id')->on('pegawais')->onDelete('cascade');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('izin');
    }
};
