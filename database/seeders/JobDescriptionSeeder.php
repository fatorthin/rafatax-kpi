<?php

namespace Database\Seeders;

use App\Models\JobDescription;
use App\Models\PositionReference;
use Illuminate\Database\Seeder;

class JobDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure positions exist
        if (PositionReference::count() === 0) {
            PositionReference::factory()->count(5)->create();
        }

        // Create 1-3 job descriptions per position
        PositionReference::all()->each(function (PositionReference $position): void {
            $numDescriptions = rand(1, 3);

            for ($i = 0; $i < $numDescriptions; $i++) {
                JobDescription::create([
                    'position_id' => $position->id,
                    'job_description' => fake()->sentence(12),
                ]);
            }
        });
    }
}

