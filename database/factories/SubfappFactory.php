<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subfapp>
 */
class SubfappFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->word();
        
        return [
            'name' => $name,
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->paragraph(),
            'type' => 'public',
            'created_by' => User::factory(),
        ];
    }
}
