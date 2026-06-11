<?php

namespace App\Domain\Doctor\Exceptions;

use Exception;

class SpecialtyNotFoundException extends Exception
{
    public function __construct(string $message = 'Especialidade não encontrada.')
    {
        parent::__construct($message);
    }
}