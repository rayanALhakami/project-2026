<?php

namespace Database\Factories;

use App\Enums\TripItemType;
use App\Models\Place;
use App\Models\TripDay;
use App\Models\TripItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TripItem>
 */
class TripItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_day_id' => TripDay::factory(),
            'place_id' => Place::factory(),
            'title' => fake()->words(3, true),
            'type' => fake()->randomElement(TripItemType::cases()),
            'start_time' => fake()->time('H:i'),
            'duration_minutes' => fake()->numberBetween(30, 180),
            'notes' => null,
            'sort_order' => 0,
        ];
    }
}
