<?php

namespace App\Domain\Patient\Exceptions;

use Exception;

class InvalidCpfException extends Exception
{
    public function __construct(string $message = 'CPF inválido.')
    {
        parent::__construct($message);
    }
}