<?php

namespace App\Domain\Price\Enums;

enum PriceType: string
{
    case DOCTOR = 'doctor';
    case SPECIALTY = 'specialty';
    case DURATION = 'duration';

    public function label(): string
    {
        return match ($this) {
            self::DOCTOR => 'Por Médico',
            self::SPECIALTY => 'Por Especialidade',
            self::DURATION => 'Por Duração',
        };
    }
}
