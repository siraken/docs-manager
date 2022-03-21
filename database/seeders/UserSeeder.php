<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // admin account
        DB::table('users')->insert([
            'name' => 'Administrator',
            'email' => 'admin@novalumo.com',
            'password' => Hash::make('novalumo'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // dummy data
        for ($i = 0; $i < 10; $i++)
        {
            DB::table('users')->insert([
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
