<?php

namespace Database\Factories;

use App\Models\AcademicClass;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nim' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'class_id' => AcademicClass::factory(),
            'fingerprint_id' => fake()->unique()->numerify('X606-S-####'),
            'status' => 'active',
        ];
    }
}






