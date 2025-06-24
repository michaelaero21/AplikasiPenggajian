<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\GajiKaryawan;

class Karyawan extends Model
{
    use HasFactory;
    protected $table = 'karyawans'; 

    protected $fillable = ['nama', 'jabatan', 'kategori_gaji', 'nomor_telepon', 'jenis_kelamin', 'alamat_karyawan'];

    public function gajiKaryawan()
    {
        return $this->hasOne(GajiKaryawan::class, 'karyawan_id');
    }
    // In Karyawan.php model

    public function absensi()
    {   
        return $this->hasMany(Absensi::class, 'karyawan_id')->latest('tanggal'); // Assuming 'karyawan_id' is the foreign key
    }
    public function delete()
    {
        // Menghapus semua absensi terkait secara permanen
        $this->absensi()->forceDelete();
        parent::delete();
    }
    public function slipGaji()
    {
        return $this->hasMany(SlipGaji::class);
    }  
    // app/Models/Karyawan.php


}
