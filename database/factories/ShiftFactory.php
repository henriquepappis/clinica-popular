<?php

namespace Database\Factories;

use App\Domain\Shift\Models\Shift;
use App\Domain\Shift\Enums\ShiftPeriod;
use App\Domain\Shift\Enums\ShiftStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShiftFactory extends Factory
{
    protected $model = Shift::class;

    public function definition(): array
    {
        $periods = [
            ShiftPeriod::MORNING,
            ShiftPeriod::AFTERNOON,
            ShiftPeriod::EVENING,
        ];

        $period = $this->faker->randomElement($periods);

        return [
            // Sufixo único evita colisão com a unique (period, name)
            // quando um teste cria vários turnos do mesmo período
            'name' => match ($period) {
                ShiftPeriod::MORNING => 'Turno da Manhã',
                ShiftPeriod::AFTERNOON => 'Turno da Tarde',
                ShiftPeriod::EVENING => 'Turno da Noite',
            } . ' ' . $this->faker->unique()->numberBetween(1, 99999),
            'period' => $period,
            'start_time' => match ($period) {
                ShiftPeriod::MORNING => '07:00',
                ShiftPeriod::AFTERNOON => '12:00',
                ShiftPeriod::EVENING => '18:00',
            },
            'end_time' => match ($period) {
                ShiftPeriod::MORNING => '12:00',
                ShiftPeriod::AFTERNOON => '18:00',
                ShiftPeriod::EVENING => '22:00',
            },
            'max_appointments' => $this->faker->numberBetween(15, 30),
            'status' => ShiftStatus::ACTIVE,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShiftStatus::INACTIVE,
        ]);
    }
}