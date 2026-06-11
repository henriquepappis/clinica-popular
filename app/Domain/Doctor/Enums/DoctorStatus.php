<?php

namespace App\Domain\Doctor\Enums;

enum DoctorStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ON_LEAVE = 'on_leave';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Ativo',
            self::INACTIVE => 'Inativo',
            self::ON_LEAVE => 'De Licença',
        };
    }
}