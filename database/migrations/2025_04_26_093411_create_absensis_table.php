<?php
// In database/migrations/xxxx_xx_xx_xxxxxx_create_absensis_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbsensisTable extends Migration
{
    public function up()
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');
            $table->string('nama_karyawan')->nullable();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();  // Untuk jam masuk
            $table->time('jam_pulang')->nullable(); 
            $table->string('status', 1)->default('I');// Untuk jam pulang
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('absensis');
    }
}
