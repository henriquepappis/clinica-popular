<?php

namespace App\Domain\Patient\Actions;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\Exceptions\DuplicatePatientException;

class CheckDuplicatePatientAction
{
    public function execute(string $cpf): void
    {
        if (Patient::where('cpf', $cpf)->exists()) {
            throw new DuplicatePatientException('Paciente com este CPF já existe.');
        }
    }
}