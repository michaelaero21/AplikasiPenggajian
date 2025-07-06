<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            'username'             => 'admin@cvam.my.id',
            'password'             => Hash::make('admin'),
            'name'                 => 'Administrator',

            // field tambahan
            'profile_photo'        => null,
            'alamat'               => null,
            'nomor_telepon'        => null,

            // role & status
            'role'                 => 'Admin',
            'status'               => 'Aktif',
            'waktu_diaktifkan'     => Carbon::now(),
            'waktu_dinonaktifkan'  => null,

            // token & timestamp
            'remember_token'       => Str::random(60),
            'created_at'           => Carbon::now(),
            'updated_at'           => Carbon::now(),
        ]);
    }
}
