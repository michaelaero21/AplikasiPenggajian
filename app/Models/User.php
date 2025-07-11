<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'alamat','nomor_telepon',
        'password',
        'role',
        'status',
        'waktu_diaktifkan',
        'waktu_dinonaktifkan',  // Menambahkan kolom 'role' ke dalam daftar yang dapat diisi
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'waktu_diaktifkan'     => 'datetime',
            'waktu_dinonaktifkan'  => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function scopeAktif($q)
    {
        return $q->where('status', 'Aktif');
    }
    /**
     * Method untuk mengecek apakah user adalah admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    /**
     * Method untuk mengecek apakah user adalah pegawai.
     */
    public function isKaryawan(): bool
    {
        return $this->role === 'Karyawan';
    }
    public function karyawan()
    {
        return $this->hasOne(Karyawan::class);
    }
    public function slipGajis()
    {
        return $this->hasMany(SlipGaji::class, 'karyawan_id');
    }
    public function getAlamatAttribute($value)
    {
        return $value ?: null;      // ubah string kosong jadi null
    }
    public function getNomorTeleponAttribute($val)
    {
        return blank($val) ? null : $val;   // string kosong → null
    }



}
