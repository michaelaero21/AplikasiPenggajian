<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlipGajisTable extends Migration
{
    public function up()
    {
        Schema::create('slip_gajis', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel karyawans
            $table->foreignId('karyawan_id')->constrained('karyawans')->onDelete('cascade');

            // Informasi Periode dan Kategori Gaji
            $table->string('periode'); // Contoh: "6-13 Juni 2025" atau "April 2025"
            $table->enum('kategori_gaji', ['mingguan', 'bulanan']);

            // Data perhitungan absensi
            $table->integer('jumlah_hadir')->default(0);
            $table->decimal('uang_makan', 15, 2)->default(0);
            $table->decimal('lembur', 15, 2)->default(0);
            $table->decimal('bonus', 15, 2)->default(0);
            $table->decimal('potongan', 15, 2)->default(0);

            // Komponen khusus untuk bulanan
            $table->decimal('gaji_pokok', 15, 2)->default(0);

            // Total akhir
            $table->decimal('total_dibayar', 15, 2)->default(0);

            // File PDF dan status pengiriman
            $table->string('file_pdf')->nullable();
            $table->enum('status_kirim', ['belum', 'terkirim'])->default('belum');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('slip_gaji');
    }
}
