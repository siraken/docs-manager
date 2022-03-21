<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create('ja_JP');

        // dummy data
        for ($i = 0; $i < 10; $i++)
        {
            DB::table('clients')->insert([
                'name' => $faker->company(),
                'title' => $faker->randomElement(['御中', '様']),
                'corp_no' => $faker->randomNumber(),
                'rep' => $faker->name(),
                'zip_code' => $faker->postcode(),
                'address' => $faker->address(),
                'tel_no' => $faker->phoneNumber(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
