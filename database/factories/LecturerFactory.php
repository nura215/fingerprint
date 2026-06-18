<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LecturerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nidn' => fake()->unique()->numerify('##########'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'fingerprint_id' => fake()->unique()->numerify('X606-L-####'),
            'status' => 'active',
        ];
    }
}






