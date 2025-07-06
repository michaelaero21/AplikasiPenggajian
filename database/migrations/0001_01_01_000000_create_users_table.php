<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Login utama
            $table->string('username')->unique();          // contoh: admin@cvam.my.id
            $table->string('password');

            // Informasi profil dasar
            $table->string('name')->nullable();
            $table->string('profile_photo')->nullable();

            // ➊ Tambahkan kolom alamat & nomor telepon di sini
            $table->text('alamat')->nullable();
            $table->string('nomor_telepon', 20)->nullable();

            // Role & status
            $table->enum('role', ['Admin', 'Karyawan'])->default('Karyawan')->index();
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif')->index();
            $table->timestamp('waktu_diaktifkan')->nullable();
            $table->timestamp('waktu_dinonaktifkan')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });

        // Password reset (berbasis username)
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Session table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
