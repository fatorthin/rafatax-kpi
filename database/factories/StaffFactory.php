<?php

namespace Database\Factories;

use App\Models\DepartmentReference;
use App\Models\PositionReference;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'birth_place' => $this->faker->city(),
            'birth_date' => $this->faker->date(),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'no_ktp' => $this->faker->unique()->numerify('################'),
            'no_spk' => $this->faker->unique()->bothify('SPK-####-????'),
            'jenjang' => $this->faker->randomElement(['SMA', 'D-3', 'D-4', 'S-1', 'S-2', 'S-3']),
            'jurusan' => $this->faker->jobTitle(),
            'university' => $this->faker->company(),
            'no_ijazah' => $this->faker->unique()->bothify('IJZ-########'),
            'tmt_training' => $this->faker->date(),
            'periode' => $this->faker->randomElement(['2024', '2025']),
            'selesai_training' => $this->faker->date(),
            'department_reference_id' => DepartmentReference::factory(),
            'position_reference_id' => PositionReference::firstOrCreate(
                ['name' => $this->faker->randomElement([
                    'Staff',
                    'Leader Operational',
                    'Junior Manager',
                    'HRD',
                    'Coordinator Operational',
                ])],
                ['description' => 'Posisi ' . $this->faker->word()]
            )->id,
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
