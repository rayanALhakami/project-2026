<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->city(),
            'name_en' => fake()->city(),
            'region' => fake()->state(),
            'latitude' => fake()->latitude(16, 32),
            'longitude' => fake()->longitude(34, 56),
            'description' => fake()->paragraph(),
            'image' => null,
        ];
    }
}
