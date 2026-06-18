<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('DPT-##'),
            'name' => fake()->randomElement(['Teknik Informatika', 'Sistem Informasi', 'Teknik Elektro']),
            'faculty' => fake()->randomElement(['Fakultas Teknik', 'Fakultas Ilmu Komputer']),
        ];
    }
}






