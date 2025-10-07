<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\DepartmentReference;
use App\Models\PositionReference;
use App\Models\Staff;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
        ]);

        // Seed reference data via factories
        DepartmentReference::factory()->count(5)->create();
        // Membuat data position dengan pilihan tertentu
        $positions = [
            'Staff',
            'Leader Operational',
            'Junior Manager',
            'HRD',
            'Coordinator Operational',
        ];

        foreach ($positions as $position) {
            PositionReference::firstOrCreate(
                ['name' => $position],
                ['description' => 'Posisi ' . $position]
            );
        }

        // Seed staff data; relations will be created by factory if not provided
        Staff::factory()->count(20)->create();

        // Seed clients
        $this->call([
            ClientSeeder::class,
            JobDescriptionSeeder::class,
        ]);
    }
}
