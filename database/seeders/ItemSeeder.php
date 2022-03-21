<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
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
            DB::table('items')->insert([
                'name' => $faker->word(),
                'unit' => $faker->randomElement(['個', '箱', '本']),
                'cost' => $faker->randomNumber(),
                'tax' => $faker->randomElement([0, 1]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
