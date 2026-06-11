<?php

namespace App\Domain\WaitingList\Enums;

enum WaitingListStatus: string
{
    case WAITING = 'waiting';
    case NOTIFIED = 'notified';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'Aguardando',
            self::NOTIFIED => 'Notificado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
