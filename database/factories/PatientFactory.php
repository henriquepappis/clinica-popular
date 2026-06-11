<?php

namespace Database\Factories;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Enums\PatientStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'cpf' => $this->generateValidCpf(),
            'birth_date' => $this->faker->dateTimeBetween('-80 years', '-18 years'),
            'phone' => $this->faker->numerify('###########'),
            'status' => PatientStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PatientStatus::INACTIVE,
        ]);
    }

    private function generateValidCpf(): string
    {
        // Gerar 9 dígitos aleatórios
        $base = str_pad($this->faker->numberBetween(0, 999999999), 9, '0', STR_PAD_LEFT);

        // Calcular primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$base[$i] * (10 - $i);
        }
        $firstVerifier = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        // Calcular segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)($base . $firstVerifier)[$i] * (11 - $i);
        }
        $secondVerifier = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $base . $firstVerifier . $secondVerifier;
    }
}