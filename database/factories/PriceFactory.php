<?php

namespace Database\Factories;

use App\Domain\Price\Models\Price;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceFactory extends Factory
{
    protected $model = Price::class;

    public function definition(): array
    {
        return [
            'doctor_id' => null,
            'specialty_id' => Specialty::factory(),
            'duration_minutes' => null,
            'value' => $this->faker->randomFloat(2, 50, 300),
        ];
    }

    public function forDoctor(): static
    {
        return $this->state(fn (array $attributes) => [
            'doctor_id' => Doctor::factory(),
            'specialty_id' => null,
        ]);
    }

    public function forDuration(int $minutes = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'doctor_id' => null,
            'specialty_id' => null,
            'duration_minutes' => $minutes,
        ]);
    }
}
