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
            'region' => fake()->randomElement([
                'منطقة الرياض',
                'منطقة مكة المكرمة',
                'منطقة المدينة المنورة',
                'منطقة القصيم',
                'منطقة الشرقية',
                'منطقة عسير',
                'منطقة تبوك',
                'منطقة حائل',
                'منطقة جازان',
                'منطقة نجران',
                'منطقة الباحة',
                'منطقة الجوف',
                'منطقة الحدود الشمالية',
            ]),
            'latitude' => fake()->latitude(16, 32),
            'longitude' => fake()->longitude(34, 56),
            'description' => fake()->paragraph(),
            'image' => null,
        ];
    }
}
