<?php

namespace App\Domain\Price\Exceptions;

use Exception;

class InvalidPriceException extends Exception
{
    public function __construct(string $message = 'Configuração de preço inválida.')
    {
        parent::__construct($message);
    }
}
