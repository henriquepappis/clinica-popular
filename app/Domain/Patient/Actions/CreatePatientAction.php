<?php

namespace App\Domain\Patient\Actions;

use App\Domain\Patient\Models\Patient;
use App\Domain\Patient\DataTransferObjects\PatientData;
use App\Domain\Patient\Events\PatientRegistered;
use App\Domain\Patient\Enums\PatientStatus;

class CreatePatientAction
{
    public function __construct(
        private ValidateCpfAction $validateCpf,
        private CheckDuplicatePatientAction $checkDuplicate,
    ) {}

    public function execute(PatientData $data): Patient
    {
        // Validações de negócio
        $this->validateCpf->execute($data->cpf);
        $this->checkDuplicate->execute($data->cpf);

        // Criar paciente
        $patient = Patient::create([
            'name' => $data->name,
            'cpf' => $data->cpf,
            'birth_date' => $data->birthDate,
            'phone' => $data->phone,
            'status' => PatientStatus::ACTIVE,
        ]);

        // Disparar evento
        event(new PatientRegistered($patient));

        return $patient;
    }
}