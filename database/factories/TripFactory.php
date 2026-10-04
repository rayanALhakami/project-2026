<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'city_id' => City::factory(),
            'title' => fake()->sentence(3),
            'start_date' => now()->addWeek(),
            'end_date' => now()->addWeek()->addDays(2),
            'travelers_count' => fake()->numberBetween(1, 6),
            'budget' => fake()->randomFloat(2, 500, 15000),
            'interests' => fake()->randomElements(['history', 'food', 'nature', 'shopping', 'adventure'], 2),
            'notes' => null,
            'share_token' => null,
            'is_public' => false,
        ];
    }

    /**
     * Indicate that the trip is shared publicly.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'share_token' => Str::random(32),
            'is_public' => true,
        ]);
    }
}
