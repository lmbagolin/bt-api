<?php

namespace Database\Factories;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Player>
 */
class PlayerFactory extends Factory
{
    public function definition(): array
    {
        $gender = fake()->randomElement(['M', 'F']);
        $firstName = $gender === 'M'
            ? fake('pt_BR')->firstNameMale()
            : fake('pt_BR')->firstNameFemale();
        $lastName = fake('pt_BR')->lastName();
        $name = "{$firstName} {$lastName}";

        return [
            'name' => $name,
            'nickname' => fake()->optional(0.7)->userName(),
            'gender' => $gender,
            'level' => fake()->randomElement(['iniciante', 'intermediário', 'avançado', 'profissional']),
            'city' => fake('pt_BR')->city(),
            'whatsapp' => fake()->optional(0.8)->numerify('(##) 9####-####'),
            'instagram' => fake()->optional(0.6)->userName(),
            'user_id' => User::factory(),
        ];
    }
}
