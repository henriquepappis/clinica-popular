<?php

namespace Database\Factories;

use App\Domain\Specialty\Models\Specialty;
use App\Domain\Specialty\Enums\SpecialtyStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    public function definition(): array
    {
        $specialties = [
            'Cardiologia',
            'Dermatologia',
            'Pediatria',
            'Neurologia',
            'Oftalmologia',
            'Clínica Geral',
            'Gastroenterologia',
            'Ortopedia',
            'Psiquiatria',
            'Pneumologia',
            'Otorrinolaringologia',
            'Reumatologia',
            'Endocrinologia',
            'Infectologia',
            'Nefrologia',
        ];

        return [
            'name' => $this->faker->unique()->randomElement($specialties),
            'description' => $this->faker->sentence(),
            'status' => SpecialtyStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SpecialtyStatus::INACTIVE,
        ]);
    }
}