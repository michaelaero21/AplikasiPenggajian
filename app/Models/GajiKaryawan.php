<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GajiKaryawan extends Model
{
    use HasFactory;

    protected $fillable = [
        'karyawan_id',
        'kategori_gaji',
        'gaji_pokok', 
        'uang_makan', 
        'uang_transportasi', 
        'asuransi', 'uang_lembur', 
        'thr', 
        'tunjangan_sewa_transport', 
        'tunjangan_pulsa', 
        'insentif'];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }
}
