<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicYearFactory extends Factory
{
    public function definition(): array
    {
        return [
            'year' => '2026/2027',
            'semester' => fake()->randomElement(['ganjil', 'genap']),
            'is_active' => false,
        ];
    }
}






