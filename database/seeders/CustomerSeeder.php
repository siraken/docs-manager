<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // initial customers array
        $customers = [
            [
                'name' => 'Novalumo',
                'is_company' => 1,
                'email' => 'novalumo@novalumo.llc',
                'phone' => '1234567890',
                'post_code' => '12345',
                'address' => '1234 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'note' => 'Novalumo LLC',
            ],
            [
                'name' => 'John Doe',
                'is_company' => 0,
                'email' => 'johndoe@example.com',
                'phone' => '1234567890',
                'post_code' => '12345',
                'address' => '1234 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'note' => 'John Doe',
            ],
        ];

        // create initial customers
        foreach ($customers as $customer) {
            DB::table('customers')->insert([
                'name' => $customer['name'],
                'is_company' => $customer['is_company'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'post_code' => $customer['post_code'],
                'address' => $customer['address'],
                'city' => $customer['city'],
                'state' => $customer['state'],
                'country' => $customer['country'],
                'note' => $customer['note'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
