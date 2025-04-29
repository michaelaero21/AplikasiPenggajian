<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jabatan', [
                'Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing', 
                'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver', 
                'Gudang', 'Helper Gudang', 'Office Girl'
            ]);
            $table->string('nomor_telepon', 20)->nullable()->unique();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('alamat_karyawan');

            $table->timestamps();
        });
    }

    /**
     * Rollback migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};

