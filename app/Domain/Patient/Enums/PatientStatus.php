<?php

namespace App\Domain\Patient\Enums;

enum PatientStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}