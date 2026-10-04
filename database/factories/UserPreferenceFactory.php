<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
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
            'locale' => 'ar',
            'default_budget' => fake()->randomFloat(2, 500, 10000),
            'travel_style' => fake()->randomElement(['relaxed', 'balanced', 'packed']),
            'interests' => fake()->randomElements(['history', 'food', 'nature', 'shopping', 'adventure'], 3),
        ];
    }
}
