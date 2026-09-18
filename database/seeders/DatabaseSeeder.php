<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Superadmin',
            'email' => 'superadmin@jalara.dev',
        ]);

        User::factory()
            ->count(100)
            ->create();

        User::factory()
            ->count(50)
            ->disabled()
            ->create();

        User::factory()
            ->count(25)
            ->suspend()
            ->create();
    }
}
