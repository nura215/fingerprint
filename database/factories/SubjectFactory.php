<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('MK###'),
            'name' => fake()->randomElement(['Pemrograman Web', 'Basis Data', 'Jaringan Komputer']),
            'sks' => fake()->numberBetween(2, 4),
            'semester' => fake()->numberBetween(1, 8),
            'department_id' => Department::factory(),
            'status' => 'active',
        ];
    }
}






