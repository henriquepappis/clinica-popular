<?php

namespace Database\Factories;

use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\Patient\Models\Patient;
use App\Domain\Specialty\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WaitingListFactory extends Factory
{
    protected $model = WaitingList::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'specialty_id' => Specialty::factory(),
            'priority' => $this->faker->numberBetween(1, 3),
            'status' => 'waiting',
            'reason' => $this->faker->sentence(),
            'added_at' => Carbon::now(),
            'notified_at' => null,
        ];
    }

    public function notified(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'notified',
            'notified_at' => Carbon::now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
