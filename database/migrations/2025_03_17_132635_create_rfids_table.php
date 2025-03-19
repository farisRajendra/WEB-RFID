<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rfids', function (Blueprint $table) {
            $table->string('rfid')->primary(); 
            $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade'); // FK to  pegawai
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfids');
    }
};
