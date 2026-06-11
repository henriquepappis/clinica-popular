<?php

namespace App\Domain\Appointment\Exceptions;

use Exception;

class DoctorUnavailableException extends Exception
{
    public function __construct(string $message = 'Médico não disponível neste horário.')
    {
        parent::__construct($message);
    }
}