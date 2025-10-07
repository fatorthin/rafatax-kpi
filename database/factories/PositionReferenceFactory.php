<?php

namespace Database\Factories;

use App\Models\PositionReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PositionReference>
 */
class PositionReferenceFactory extends Factory
{
    protected $model = PositionReference::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->jobTitle(),
            'description' => $this->faker->sentence(),
        ];
    }
}


