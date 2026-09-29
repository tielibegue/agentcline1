<?php

namespace Database\Factories;

use App\Models\Juridiction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Juridiction>
 */
class JuridictionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('JUR-?????')),
            'libelle' => fake()->randomElement([
                'Tribunal de commerce',
                'Tribunal de première instance',
                "Cour d'appel",
                'Tribunal du travail',
            ]).' de '.fake()->city(),
            'type' => fake()->randomElement(array_keys(Juridiction::TYPES)),
            'ville' => fake()->city(),
            'telephone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'actif' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributs): array => ['actif' => false]);
    }
}
