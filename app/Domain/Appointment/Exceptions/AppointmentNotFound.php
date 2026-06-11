<?php

namespace App\Domain\Appointment\Exceptions;

use Exception;

class AppointmentNotFoundException extends Exception
{
    public function __construct(string $message = 'Agendamento não encontrado.')
    {
        parent::__construct($message);
    }
}