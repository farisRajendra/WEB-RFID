<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absences MODIFY COLUMN status ENUM('hadir','terlambat','pulang_awal','pulang_duplikat','masuk','masuk_duplikat') NOT NULL DEFAULT 'hadir'");
    }

    public function down(): void
    {
        // Kembalikan enum ke yang lama, misal ini enum sebelumnya
        DB::statement("ALTER TABLE absences MODIFY COLUMN status ENUM('hadir','izin','sakit','alfa','tidak hadir') NOT NULL DEFAULT 'hadir'");
    }
};
