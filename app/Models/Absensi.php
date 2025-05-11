<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'karyawan_id',
        'nama_karyawan',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status'
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    protected static function booted()
    {
        static::saving(function ($absensi) {
            // Otomatis set status berdasarkan data jam masuk & jam pulang
            $absensi->status = ($absensi->jam_masuk && $absensi->jam_pulang) ? 'H' : 'I';
        });
    }
}
