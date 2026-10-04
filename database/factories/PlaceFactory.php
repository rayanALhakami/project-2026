<?php

namespace Database\Factories;

use App\Enums\PlaceCategory;
use App\Enums\TimeOfDay;
use App\Models\City;
use App\Models\Place;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Place>
 */
class PlaceFactory extends Factory
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
            'name' => fake()->words(2, true),
            'name_en' => fake()->words(2, true),
            'category' => fake()->randomElement(PlaceCategory::cases()),
            'description' => fake()->paragraph(),
            'latitude' => fake()->latitude(16, 32),
            'longitude' => fake()->longitude(34, 56),
            'image' => null,
            'ticket_price' => fake()->optional(0.6)->randomFloat(2, 0, 250),
            'rating' => fake()->randomFloat(1, 3, 5),
            'opening_hours' => '9:00 - 23:00',
            'tags' => fake()->randomElements(['family', 'indoor', 'historic', 'view'], 2),
            'best_time' => fake()->randomElement(TimeOfDay::cases()),
            'avg_visit_duration' => fake()->numberBetween(45, 240),
            'is_indoor' => fake()->boolean(),
            'family_friendly' => fake()->boolean(70),
            'wheelchair_accessible' => fake()->boolean(50),
            'prayer_facilities' => fake()->boolean(50),
            'closed_friday' => fake()->boolean(20),
        ];
    }
}
