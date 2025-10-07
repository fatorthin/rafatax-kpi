<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat user admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@rafatax.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
            ]
        );
        
        // Assign role admin
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Buat user staff
        $staff = User::firstOrCreate(
            ['email' => 'staff@rafatax.com'],
            [
                'name' => 'Staff User',
                'password' => Hash::make('password123'),
            ]
        );
        
        // Assign role staff
        if (!$staff->hasRole('staff')) {
            $staff->assignRole('staff');
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Admin: admin@rafatax.com / password123');
        $this->command->info('Staff: staff@rafatax.com / password123');
    }
}
