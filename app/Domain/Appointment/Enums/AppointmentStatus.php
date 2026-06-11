<?php

namespace App\Domain\Appointment\Enums;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case CONFIRMED = 'confirmed';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Agendado',
            self::CONFIRMED => 'Confirmado',
            self::COMPLETED => 'Realizado',
            self::CANCELLED => 'Cancelado',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::SCHEDULED, self::CONFIRMED]);
    }
}