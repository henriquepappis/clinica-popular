<?php

namespace App\Domain\Patient\Exceptions;

use Exception;

class DuplicatePatientException extends Exception
{
    public function __construct(string $message = 'Paciente duplicado.')
    {
        parent::__construct($message);
    }
}