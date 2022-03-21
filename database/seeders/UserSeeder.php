<?php

namespace Database\Seeders;

use Faker\Factory;
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
        $faker = Factory::create('ja_JP');
        for ($i = 0; $i < 5; $i++)
        {
            DB::table('users')->insert([
                'name' => $faker->name(),
                'email' => $faker->email(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
