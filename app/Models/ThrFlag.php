<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThrFlag extends Model
{
    use HasFactory;

    protected $table = 'thr_flags';           // nama tabel

    /** kolom yang boleh di‐isi massal */
    protected $fillable = [
        'karyawan_id',     // relasi ke tabel karyawans
        'periode',         // Y-m (bulanan)  atau  Y-m-d (mingguan — Senin)
        'kategori', 
        'processed_at',       // 'bulanan' | 'mingguan'
    ];

    /* ---------- relasi convenience ---------- */
    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
