<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM menjadi string sementara
        DB::statement("ALTER TABLE absences MODIFY status VARCHAR(50)");

        // Ubah isi status lama ke format baru jika perlu (opsional, tergantung datamu)
        DB::table('absences')->where('status', 'masuk')->update(['status' => 'hadir']);
        DB::table('absences')->where('status', 'masuk_duplikat')->update(['status' => 'terlambat']);
        DB::table('absences')->where('status', 'pulang_duplikat')->update(['status' => 'pulang_awal']);
        // Tambahan konversi jika perlu

        // Ubah lagi kolom menjadi enum baru
        DB::statement("ALTER TABLE absences MODIFY status ENUM('hadir', 'terlambat', 'pulang_awal', 'terlambat_dan_pulang_awal') DEFAULT 'hadir'");
    }

    public function down(): void
    {
        // Balikkan ke enum lama jika rollback
        DB::statement("ALTER TABLE absences MODIFY status ENUM('hadir','terlambat','pulang_awal','pulang_duplikat','masuk','masuk_duplikat') DEFAULT 'hadir'");
    }
};

