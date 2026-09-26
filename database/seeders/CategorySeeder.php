<?php

namespace Database\Seeders;

use App\Actions\CreateDefaultCategories;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::firstOrFail();

        app(CreateDefaultCategories::class)->handle($user);
    }
}
