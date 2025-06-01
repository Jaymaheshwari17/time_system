<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('user_master')->insert([
            'name' => 'Jay',
            'email' => 'jay@gmail.com',
            'password' => Hash::make('Jay@123'),
            'is_active' => '1',
            'is_admin' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
