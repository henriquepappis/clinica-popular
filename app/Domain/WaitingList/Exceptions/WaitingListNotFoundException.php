<?php

namespace App\Domain\WaitingList\Exceptions;

use Exception;

class WaitingListNotFoundException extends Exception
{
    public function __construct(string $message = 'Registro de espera não encontrado.')
    {
        parent::__construct($message);
    }
}
