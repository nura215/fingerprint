<?php

namespace Database\Factories;

use App\Models\AcademicClass;
use App\Models\AcademicYear;
use App\Models\Lecturer;
use App\Models\Room;
use App\Models\Subject;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(7, 15);

        return [
            'academic_year_id' => AcademicYear::factory(),
            'lecturer_id' => Lecturer::factory(),
            'class_id' => AcademicClass::factory(),
            'subject_id' => Subject::factory(),
            'room_id' => Room::factory(),
            'day' => fake()->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday']),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $startHour + 2),
            'status' => 'active',
        ];
    }
}






