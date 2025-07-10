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
        Schema::create('thr_flags', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id');
            $table->string('periode'); // Bisa Y-m (bulanan) atau Y-m-d (mingguan)
            $table->enum('kategori', ['bulanan', 'mingguan']);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->unique(['karyawan_id', 'periode', 'kategori']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thr_flags');
    }
};
