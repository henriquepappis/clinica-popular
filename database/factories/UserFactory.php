<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->phoneNumber(),
            'role' => UserRole::PATIENT,
            'is_active' => true,
        ];
    }

    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }

    public function doctor(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::DOCTOR,
        ]);
    }

    public function receptionist(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => UserRole::RECEPTIONIST,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
