<?php

namespace App\Domain\Appointment\Exceptions;

use Exception;

class ShiftFullException extends Exception
{
    public function __construct(string $message = 'Este turno está cheio.')
    {
        parent::__construct($message);
    }
}