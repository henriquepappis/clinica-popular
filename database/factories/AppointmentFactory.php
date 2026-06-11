<?php

namespace Database\Factories;

use App\Domain\Appointment\Models\Appointment;
use App\Domain\Appointment\Enums\AppointmentStatus;
use App\Domain\Patient\Models\Patient;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Shift\Models\Shift;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'shift_id' => Shift::factory(),
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'appointment_time' => $this->faker->time('H:i'),
            'status' => AppointmentStatus::SCHEDULED,
            'notes' => $this->faker->sentence(),
        ];
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AppointmentStatus::CONFIRMED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AppointmentStatus::CANCELLED,
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }
}