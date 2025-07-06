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

        $table->foreignId('karyawan_id')
            ->constrained('karyawans')
            ->onDelete('cascade');

        // Periode & kategori
        $table->string('periode');                 // "6-13 Juni 2025" / "April 2025"
        $table->enum('kategori_gaji',['mingguan','bulanan']);

        // Absensi & harian
        $table->integer('jumlah_hadir')->default(0);
        $table->decimal('uang_makan', 15, 2)->default(0);
        $table->decimal('uang_transport', 15, 2)->default(0);
        $table->decimal('lembur', 15, 2)->default(0);
        $table->decimal('bonus', 15, 2)->default(0);

        // Komponen mingguan/bulanan
        $table->decimal('gaji_pokok', 15, 2)->default(0);
        $table->decimal('thr',        15, 2)->default(0);
        $table->decimal('tunjangan_pulsa', 15, 2)->default(0);
        $table->decimal('tunjangan_sewa',  15, 2)->default(0);
        $table->decimal('asuransi',        15, 2)->default(0);
        $table->decimal('insentif',        15, 2)->default(0);

        // Total akhir
        $table->decimal('total_dibayar', 15, 2)->default(0);

        // Dokumen & status
        $table->string('file_pdf')->nullable();
        $table->enum('status_kirim',['belum','terkirim'])->default('belum');

        $table->timestamps();
    });

    }

    public function down()
    {
        Schema::dropIfExists('slip_gaji');
    }
}
