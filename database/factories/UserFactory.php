<?php

namespace Database\Factories;

use App\Enums\RoleUtilisateur;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => RoleUtilisateur::JURIDICTION,
            'actif' => true,
        ];
    }

    public function administrateur(): static
    {
        return $this->state(fn (array $attributs): array => [
            'role' => RoleUtilisateur::ADMIN,
            'juridiction_id' => null,
        ]);
    }

    public function support(): static
    {
        return $this->state(fn (array $attributs): array => [
            'role' => RoleUtilisateur::SUPPORT,
            'juridiction_id' => null,
        ]);
    }

    public function inactif(): static
    {
        return $this->state(fn (array $attributs): array => ['actif' => false]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
