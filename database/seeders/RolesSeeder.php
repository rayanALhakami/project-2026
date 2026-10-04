<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Seed the application roles.
     */
    public function run(): void
    {
        Role::findOrCreate('admin', 'web');
    }
}
