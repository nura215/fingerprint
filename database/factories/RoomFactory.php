<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('LAB-##'),
            'name' => 'Laboratorium '.fake()->word(),
            'location' => fake()->randomElement(['Gedung A Lantai 1', 'Gedung B Lantai 2']),
            'capacity' => fake()->numberBetween(20, 40),
            'status' => 'active',
        ];
    }
}






