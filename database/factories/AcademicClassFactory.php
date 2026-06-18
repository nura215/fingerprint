<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicClassFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('TI-#?'),
            'name' => 'Teknik Informatika '.fake()->randomElement(['1A', '2A', '3A']),
            'department_id' => Department::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'status' => 'active',
        ];
    }
}






