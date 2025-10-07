<?php

namespace Database\Factories;

use App\Models\DepartmentReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\DepartmentReference>
 */
class DepartmentReferenceFactory extends Factory
{
    protected $model = DepartmentReference::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'description' => $this->faker->sentence(),
        ];
    }
}


