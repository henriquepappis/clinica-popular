<?php

namespace App\Domain\Doctor\Exceptions;

use Exception;

class DuplicateCrmException extends Exception
{
    public function __construct(string $message = 'CRM duplicado.')
    {
        parent::__construct($message);
    }
}