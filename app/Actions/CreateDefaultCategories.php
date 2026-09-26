<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\User;

class CreateDefaultCategories
{
    /**
     * @var array<int, array{name: string, type: string, color: string, icon: string}>
     */
    private const DEFAULT_CATEGORIES = [
        ['name' => 'بقالة', 'type' => Category::TYPE_EXPENSE, 'color' => '#34c759', 'icon' => '🛒'],
        ['name' => 'مطاعم', 'type' => Category::TYPE_EXPENSE, 'color' => '#ff9500', 'icon' => '🍽️'],
        ['name' => 'فواتير', 'type' => Category::TYPE_EXPENSE, 'color' => '#ff3b30', 'icon' => '🧾'],
        ['name' => 'تسوق', 'type' => Category::TYPE_EXPENSE, 'color' => '#5856d6', 'icon' => '🛍️'],
        ['name' => 'مواصلات', 'type' => Category::TYPE_EXPENSE, 'color' => '#5ac8fa', 'icon' => '🚗'],
        ['name' => 'ترفيه', 'type' => Category::TYPE_EXPENSE, 'color' => '#af52de', 'icon' => '🎬'],
        ['name' => 'صحة', 'type' => Category::TYPE_EXPENSE, 'color' => '#ff2d55', 'icon' => '🏥'],
        ['name' => 'تعليم', 'type' => Category::TYPE_EXPENSE, 'color' => '#0a84ff', 'icon' => '📚'],
        ['name' => 'راتب', 'type' => Category::TYPE_INCOME, 'color' => '#34c759', 'icon' => '💼'],
        ['name' => 'عمل حر', 'type' => Category::TYPE_INCOME, 'color' => '#0a84ff', 'icon' => '🧑‍💻'],
        ['name' => 'استثمار', 'type' => Category::TYPE_INCOME, 'color' => '#30d158', 'icon' => '📈'],
        ['name' => 'هدية', 'type' => Category::TYPE_INCOME, 'color' => '#ff9f0a', 'icon' => '🎁'],
    ];

    /**
     * Create the canonical default categories for the given user.
     */
    public function handle(User $user): void
    {
        foreach (self::DEFAULT_CATEGORIES as $category) {
            $user->categories()->firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                ],
                [
                    'color' => $category['color'],
                    'icon' => $category['icon'],
                ],
            );
        }
    }
}
