<?php

namespace App\Domain\Specialty\Enums;

enum SpecialtyStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativa',
            self::INACTIVE => 'Inativa',
        };
    }
}