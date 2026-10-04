<?php

namespace Database\Factories;

use App\Models\Place;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'place_id' => Place::factory(),
            'author' => fake()->name(),
            'rating' => fake()->numberBetween(1, 5),
            'content' => fake()->paragraph(),
            'source' => fake()->randomElement(['google', 'tripadvisor', 'manual']),
            'reviewed_at' => now()->subDays(fake()->numberBetween(1, 90)),
        ];
    }
}
