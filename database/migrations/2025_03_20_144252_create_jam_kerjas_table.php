<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('jam_kerjas', function (Blueprint $table) {
            $table->id();
            $table->time('jam_masuk')->default('08:00:00');
            $table->time('jam_keluar')->default('21:00:00');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jam_kerjas');
    }
};
