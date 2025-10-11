<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->userName(), // Membuat username palsu yang unik
            'username' => fake()->unique()->userName(), // <-- Tambahkan ini
            'email' => fake()->unique()->safeEmail(), // Membuat email palsu yang unik
            'email_verified_at' => now(),
            'password' => 'password', // Kita set password default 'password' agar mudah diingat untuk testing
            'remember_token' => Str::random(10),
        ];
    }

    // ... sisa kode
}
