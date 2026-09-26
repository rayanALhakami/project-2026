<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @var array<int, string>
     */
    protected static array $expenseTitles = [
        'مشتريات بقالة',
        'عشاء في مطعم',
        'فاتورة كهرباء',
        'تذكرة سينما',
        'تعبئة وقود',
        'مشتريات ملابس',
        'دواء من الصيدلية',
        'اشتراك إنترنت',
    ];

    /**
     * @var array<int, string>
     */
    protected static array $incomeTitles = [
        'راتب شهري',
        'دخل عمل حر',
        'أرباح استثمار',
        'هدية نقدية',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([Transaction::TYPE_EXPENSE, Transaction::TYPE_INCOME]);

        return [
            'user_id' => User::factory(),
            'category_id' => null,
            'title' => fake()->randomElement($this->titlesFor($type)),
            'amount' => fake()->randomFloat(2, 5, 1500),
            'type' => $type,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
        ];
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_EXPENSE,
            'title' => fake()->randomElement(static::$expenseTitles),
        ]);
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_INCOME,
            'title' => fake()->randomElement(static::$incomeTitles),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function titlesFor(string $type): array
    {
        return $type === Transaction::TYPE_EXPENSE
            ? static::$expenseTitles
            : static::$incomeTitles;
    }
}
