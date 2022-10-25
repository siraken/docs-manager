<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // initial accounts array
        $accounts = [
            [
                'name' => 'Administrator',
                'email' => 'admin@novalumo.com',
                'password' => 'novalumo',
            ],
            [
                'name' => 'Guest',
                'email' => 'guest@novalumo.dev',
                'password' => 'guest',
            ],
        ];

        // create initial accounts
        foreach ($accounts as $account) {
            DB::table('users')->insert([
                'name' => $account['name'],
                'email' => $account['email'],
                'password' => Hash::make($account['password']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
