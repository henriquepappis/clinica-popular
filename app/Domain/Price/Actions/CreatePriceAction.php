<?php

namespace App\Domain\Price\Actions;

use App\Domain\Price\Models\Price;
use App\Domain\Price\DataTransferObjects\PriceData;
use App\Domain\Price\Events\PriceCreated;
use App\Domain\Price\Exceptions\InvalidPriceException;
use App\Domain\Doctor\Models\Doctor;
use App\Domain\Specialty\Models\Specialty;

class CreatePriceAction
{
    public function execute(PriceData $data): Price
    {
        // Validar valor positivo
        if ($data->value <= 0) {
            throw new InvalidPriceException('O valor do preço deve ser maior que zero.');
        }

        // Preço precisa de pelo menos um alvo (médico, especialidade ou duração)
        if (!$data->doctorId && !$data->specialtyId && !$data->durationMinutes) {
            throw new InvalidPriceException(
                'Informe médico, especialidade ou duração para configurar o preço.'
            );
        }

        // Validar se médico existe
        if ($data->doctorId && !Doctor::find($data->doctorId)) {
            throw new \Exception('Médico não encontrado.');
        }

        // Validar se especialidade existe
        if ($data->specialtyId && !Specialty::find($data->specialtyId)) {
            throw new \Exception('Especialidade não encontrada.');
        }

        // Verificar se já existe preço para esta combinação
        $existing = Price::where('doctor_id', $data->doctorId)
            ->where('specialty_id', $data->specialtyId)
            ->where('duration_minutes', $data->durationMinutes)
            ->exists();

        if ($existing) {
            throw new InvalidPriceException(
                'Já existe um preço configurado para esta combinação.'
            );
        }

        $price = Price::create([
            'doctor_id' => $data->doctorId,
            'specialty_id' => $data->specialtyId,
            'duration_minutes' => $data->durationMinutes,
            'value' => $data->value,
        ]);

        event(new PriceCreated($price));

        return $price;
    }
}
