<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'         => User::factory(),
            'name'              => $this->faker->randomElement(['Fire Extinguisher', 'Smoke Detector', 'Fire Alarm']),
            'type'                => $this->faker->randomElement(['water', 'foam', 'CO2', 'DCP']),
            'type_capacity'         => $this->faker->bothify('##kg'),
            'serial_number'         => $this->faker->unique()->uuid,
            'status'              => $this->faker->randomElement(['Active', 'Expired', 'Needs Maintenance']),
            'location'          => $this->faker->address,
            'assigned_to'     => null,                 // Can be adjusted

        ];
    }
}
