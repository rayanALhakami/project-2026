<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\TripDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripDay>
 */
class TripDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'day_number' => 1,
            'date' => now()->addWeek(),
            'title' => fake()->words(2, true),
            'notes' => null,
        ];
    }
}
