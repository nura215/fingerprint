<?php

namespace Database\Factories;

use App\Models\Device;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BiometricEnrollment>
 */
class BiometricEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_type' => 'student',
            'user_id' => Student::factory(),
            'fingerprint_id' => 'FP-'.fake()->unique()->bothify('#####'),
            'device_id' => Device::factory(),
            'enrolled_at' => now(),
            'status' => 'enrolled',
        ];
    }
}






