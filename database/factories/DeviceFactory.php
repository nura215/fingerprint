<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'device_code' => 'DEV-'.fake()->unique()->bothify('####'),
            'name' => 'Fingerprint '.fake()->word(),
            'model' => 'Solution X606-S',
            'ip_address' => fake()->ipv4(),
            'port' => 4370,
            'room_id' => Room::factory(),
            'connection_type' => 'tcp_ip',
            'status' => fake()->randomElement(['online', 'offline', 'maintenance']),
            'last_online_at' => null,
        ];
    }
}






