<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        $expenseCategories = $user->categories()->where('type', Category::TYPE_EXPENSE)->get();
        $salaryCategory = $user->categories()
            ->where('type', Category::TYPE_INCOME)
            ->where('name', 'راتب')
            ->first();

        for ($i = 0; $i < 140; $i++) {
            Transaction::factory()
                ->expense()
                ->create([
                    'user_id' => $user->id,
                    'category_id' => $expenseCategories->random()->id,
                    'date' => now()->subDays(random_int(0, 180))->format('Y-m-d'),
                ]);
        }

        for ($i = 0; $i < 20; $i++) {
            Transaction::factory()
                ->expense()
                ->create([
                    'user_id' => $user->id,
                    'category_id' => $expenseCategories->random()->id,
                    'date' => now()->subDays(random_int(0, 25))->format('Y-m-d'),
                ]);
        }

        for ($i = 0; $i < 6; $i++) {
            Transaction::factory()
                ->income()
                ->create([
                    'user_id' => $user->id,
                    'category_id' => $salaryCategory?->id,
                    'title' => 'راتب شهري',
                    'amount' => 10000,
                    'date' => now()->startOfMonth()->subMonths($i)->format('Y-m-d'),
                ]);
        }
    }
}
