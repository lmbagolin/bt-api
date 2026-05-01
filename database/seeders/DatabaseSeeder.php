<?php

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

        $this->call([
            CountrySeeder::class,
            StateSeeder::class,
            CitySeeder::class,
        ]);

        $user = User::updateOrCreate(
            ['email' => 'lmbagolin@gmail.com'],
            [
                'name' => 'Leonel Bagolin',
                'password' => bcrypt('PONTUAbag23@'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
