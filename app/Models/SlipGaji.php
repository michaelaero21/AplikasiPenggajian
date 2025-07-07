<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlipGaji extends Model
{
    // Tambahkan jika tidak menggunakan timestamps (jika tidak ada created_at dan updated_at di tabel)
    // public $timestamps = false;

    // Jika nama tabel berbeda dari default (slip_gajis), pastikan ini ditambahkan:
    // protected $table = 'slip_gajis';

    // Jika Anda ingin menetapkan kolom yang dapat diisi secara massal (optional tapi aman):
    protected $fillable = [
        'karyawan_id',
        'periode',
        'kategori_gaji',
        'jumlah_hadir',
        'uang_makan',
        'uang_transport',
        'tunjangan_pulsa',
        'tunjangan_sewa',
        'lembur',
        'bonus',
        'thr',
        'insentif',
        'asuransi',
        'gaji_pokok',
        'total_dibayar',
        'file_pdf',
        'status_kirim',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

}

