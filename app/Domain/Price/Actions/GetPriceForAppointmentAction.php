<?php

namespace App\Domain\Price\Actions;

use App\Domain\Price\Models\Price;
use App\Domain\Doctor\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;

class GetPriceForAppointmentAction
{
    /**
     * Seleção de preço (nesta ordem):
     * 1. Preço específico do médico
     * 2. Preço da especialidade da consulta
     * 3. Preço por duração (configuração geral)
     * 4. Preço padrão da clínica (fallback)
     */
    public function execute(string $doctorId, ?string $specialtyId = null, ?int $durationMinutes = null): float
    {
        $doctor = Doctor::find($doctorId);

        if (!$doctor) {
            throw new \Exception('Médico não encontrado.');
        }

        // 1. Preço específico do médico
        $price = $this->resolveByDuration(
            Price::query()->forDoctor($doctor->id),
            $durationMinutes
        );

        // 2. Preço da especialidade (se não houver preço do médico)
        if (!$price) {
            $specialty_id = $specialtyId ?? $this->resolveSingleSpecialty($doctor);

            if ($specialty_id) {
                $price = $this->resolveByDuration(
                    Price::query()->forSpecialty($specialty_id),
                    $durationMinutes
                );
            }
        }

        // 3. Preço geral por duração
        if (!$price && $durationMinutes) {
            $price = Price::query()
                ->whereNull('doctor_id')
                ->whereNull('specialty_id')
                ->forDuration($durationMinutes)
                ->first();
        }

        // 4. Preço padrão da clínica (fallback)
        return $price
            ? (float) $price->value
            : (float) config('clinica.default_appointment_price');
    }

    /**
     * Médico pode ter várias especialidades; sem a especialidade da
     * consulta informada, só dá para inferir quando ele tem apenas uma.
     */
    private function resolveSingleSpecialty(Doctor $doctor): ?string
    {
        $specialty_ids = $doctor->specialties()->pluck('specialties.id');

        return $specialty_ids->count() === 1 ? $specialty_ids->first() : null;
    }

    /**
     * Dentro do mesmo nível (médico/especialidade), preço com duração
     * exata tem prioridade sobre o preço sem duração definida.
     */
    private function resolveByDuration(Builder $query, ?int $durationMinutes): ?Price
    {
        if ($durationMinutes) {
            $exact = (clone $query)->forDuration($durationMinutes)->first();

            if ($exact) {
                return $exact;
            }
        }

        return $query->whereNull('duration_minutes')->first();
    }
}
