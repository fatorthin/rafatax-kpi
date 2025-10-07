<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        $grades = ['A', 'B', 'C'];
        $types = ['pt', 'kkp'];
        $statuses = ['active', 'inactive'];

        for ($i = 1; $i <= 20; $i++) {
            Client::firstOrCreate(
                [
                    'code' => 'CL-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                ],
                [
                    'company_name' => $faker->unique()->company(),
                    'address' => $faker->address(),
                    'email' => $faker->unique()->companyEmail(),
                    'phone' => $faker->phoneNumber(),
                    'owner_name' => $faker->name(),
                    'owner_role' => $faker->jobTitle(),
                    'contact_person' => $faker->name(),
                    'npwp' => $faker->optional()->numerify('##.###.###.#-###.###'),
                    'jenis_wp' => $faker->randomElement(['perseorangan', 'badan']),
                    'grade' => $faker->randomElement($grades),
                    'pph_25_reporting' => $faker->boolean(40),
                    'pph_23_reporting' => $faker->boolean(40),
                    'pph_21_reporting' => $faker->boolean(40),
                    'pph_4_reporting' => $faker->boolean(40),
                    'ppn_reporting' => $faker->boolean(40),
                    'status' => $faker->randomElement($statuses),
                    'type' => $faker->randomElement($types),
                ]
            );
        }
    }
}


