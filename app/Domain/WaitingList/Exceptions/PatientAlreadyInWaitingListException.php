<?php

namespace App\Domain\WaitingList\Exceptions;

use Exception;

class PatientAlreadyInWaitingListException extends Exception
{
    public function __construct(string $message = 'Paciente já está na fila de espera.')
    {
        parent::__construct($message);
    }
}
