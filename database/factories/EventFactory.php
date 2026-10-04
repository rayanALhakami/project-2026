<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeeks(2),
            'image' => null,
            'url' => null,
        ];
    }
}
