<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ApiTestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create API test user
        $user = User::firstOrCreate(
            ['email' => 'api@test.com'],
            [
                'name' => 'API Test User',
                'password' => Hash::make('password123'),
            ]
        );

        echo "API Test User created/found:\n";
        echo "Email: api@test.com\n";
        echo "Password: password123\n";
    }
}
