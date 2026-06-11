<?php

namespace App\Domain\Price\Exceptions;

use Exception;

class PriceNotFoundException extends Exception
{
    public function __construct(string $message = 'Preço não encontrado.')
    {
        parent::__construct($message);
    }
}
