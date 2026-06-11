<?php

namespace Database\Factories;

use App\Domain\Doctor\Models\Doctor;
use App\Domain\Doctor\Enums\DoctorStatus;
use App\Domain\Specialty\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name('male'),
            'crm' => $this->generateUniqueCrm(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('###########'),
            'bio' => $this->faker->sentence(),
            'status' => DoctorStatus::ACTIVE,
        ];
    }

    public function withSpecialties(Specialty ...$specialties): static
    {
        return $this->afterCreating(function (Doctor $doctor) use ($specialties) {
            $ids = $specialties
                ? collect($specialties)->pluck('id')->all()
                : [Specialty::factory()->create()->id];

            $doctor->specialties()->attach($ids);
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DoctorStatus::INACTIVE,
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => DoctorStatus::ON_LEAVE,
        ]);
    }

    private function generateUniqueCrm(): string
    {
        do {
            $crm = str_pad(random_int(1000, 999999), 6, '0', STR_PAD_LEFT);
        } while (Doctor::where('crm', $crm)->exists());

        return $crm;
    }
}
