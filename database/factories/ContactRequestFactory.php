<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\ContactRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactRequest>
 */
class ContactRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->numerify('05########'),
            'city_id' => City::factory(),
            'start_date' => now()->addWeeks(2),
            'travelers' => fake()->numberBetween(1, 6),
            'budget' => fake()->randomFloat(2, 1000, 15000),
            'notes' => fake()->optional()->sentence(),
            'handled_at' => null,
        ];
    }
}
