<?php

namespace App\Domain\Shift\Enums;

enum ShiftPeriod: string
{
    case MORNING = 'morning';
    case AFTERNOON = 'afternoon';
    case EVENING = 'evening';

    public function label(): string
    {
        return match ($this) {
            self::MORNING => 'Manhã',
            self::AFTERNOON => 'Tarde',
            self::EVENING => 'Noite',
        };
    }

    public function timeRange(): string
    {
        return match ($this) {
            self::MORNING => '07:00 - 12:00',
            self::AFTERNOON => '12:00 - 18:00',
            self::EVENING => '18:00 - 22:00',
        };
    }
}