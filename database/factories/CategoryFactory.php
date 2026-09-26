<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @var array<int, array{name: string, color: string, icon: string}>
     */
    protected static array $expenseCategories = [
        ['name' => 'بقالة', 'color' => '#34c759', 'icon' => '🛒'],
        ['name' => 'مطاعم', 'color' => '#ff9500', 'icon' => '🍽️'],
        ['name' => 'فواتير', 'color' => '#ff3b30', 'icon' => '🧾'],
        ['name' => 'تسوق', 'color' => '#5856d6', 'icon' => '🛍️'],
        ['name' => 'مواصلات', 'color' => '#5ac8fa', 'icon' => '🚗'],
        ['name' => 'ترفيه', 'color' => '#af52de', 'icon' => '🎬'],
        ['name' => 'صحة', 'color' => '#ff2d55', 'icon' => '🏥'],
        ['name' => 'تعليم', 'color' => '#0a84ff', 'icon' => '📚'],
    ];

    /**
     * @var array<int, array{name: string, color: string, icon: string}>
     */
    protected static array $incomeCategories = [
        ['name' => 'راتب', 'color' => '#34c759', 'icon' => '💼'],
        ['name' => 'عمل حر', 'color' => '#0a84ff', 'icon' => '🧑‍💻'],
        ['name' => 'استثمار', 'color' => '#30d158', 'icon' => '📈'],
        ['name' => 'هدية', 'color' => '#ff9f0a', 'icon' => '🎁'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return $this->forType(
            fake()->randomElement([Category::TYPE_EXPENSE, Category::TYPE_INCOME]),
        );
    }

    public function expense(): static
    {
        return $this->state(fn () => $this->forType(Category::TYPE_EXPENSE));
    }

    public function income(): static
    {
        return $this->state(fn () => $this->forType(Category::TYPE_INCOME));
    }

    /**
     * @return array<string, mixed>
     */
    private function forType(string $type): array
    {
        $pool = $type === Category::TYPE_EXPENSE
            ? static::$expenseCategories
            : static::$incomeCategories;
        $category = fake()->randomElement($pool);

        return [
            'user_id' => User::factory(),
            'name' => $category['name'],
            'type' => $type,
            'color' => $category['color'],
            'icon' => $category['icon'],
        ];
    }
}
