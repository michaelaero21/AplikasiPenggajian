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
        return $this->hasMany(Absensi::class, 'karyawan_id'); // Assuming 'karyawan_id' is the foreign key
    }

}
