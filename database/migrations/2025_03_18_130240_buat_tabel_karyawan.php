<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Buat tabel karyawans.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel users (nullable → bisa diisi belakangan)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained()          // references('id')->on('users')
                  ->nullOnDelete();        // jika user dihapus, set user_id = NULL

            $table->string('nama');
            $table->enum('jabatan', [
                'Accounting', 'Admin', 'Admin Penjualan', 'Finance', 'Admin Purchasing',
                'Head Gudang', 'Admin Gudang', 'Supervisor', 'Marketing', 'Driver',
                'Gudang', 'Helper Gudang', 'Office Girl'
            ]);

            $table->string('nomor_telepon', 20)->nullable()->unique();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('alamat_karyawan');

            // Status aktif / nonaktif
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');

            $table->timestamps();
        });
    }

    /**
     * Hapus tabel karyawans.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
