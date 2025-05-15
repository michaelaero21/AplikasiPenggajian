<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gaji_karyawans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade'); // Pastikan tabel karyawans ada
            $table->enum('kategori_gaji', ['Mingguan', 'Bulanan']);
            // Komponen gaji untuk karyawan non-marketing (WAJIB DIISI)
            $table->decimal('gaji_pokok', 10, 2); // Wajib
            $table->decimal('uang_makan', 10, 2); // Wajib
            $table->decimal('asuransi', 10, 2); // Wajib
            $table->decimal('uang_transportasi', 10, 2); // Wajib

            // Uang lembur, THR tidak wajib diisi
            $table->decimal('uang_lembur', 10, 2)->nullable();
            $table->decimal('thr', 10, 2)->nullable();

            // Komponen gaji khusus untuk marketing (WAJIB DIISI kecuali insentif)
            $table->decimal('tunjangan_sewa_transport', 10, 2)->nullable(); 
            $table->decimal('tunjangan_pulsa', 10, 2)->nullable(); // Wajib

            // Insentif tidak wajib diisi
            $table->decimal('insentif', 10, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gaji_karyawans');
    }
};
