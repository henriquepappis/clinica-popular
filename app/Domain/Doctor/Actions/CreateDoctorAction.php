<?php

namespace App\Domain\Doctor\Actions;

use App\Domain\Doctor\Models\Doctor;
use App\Domain\Doctor\DataTransferObjects\DoctorData;
use App\Domain\Doctor\Events\DoctorCreated;
use App\Domain\Doctor\Enums\DoctorStatus;
use App\Domain\Specialty\Models\Specialty;
use App\Domain\Doctor\Exceptions\SpecialtyNotFoundException;
use App\Domain\Doctor\Exceptions\DuplicateCrmException;
use Illuminate\Support\Str;

class CreateDoctorAction
{
    public function execute(DoctorData $data): Doctor
    {
        // Médico precisa de ao menos uma especialidade
        if (empty($data->specialtyIds)) {
            throw new SpecialtyNotFoundException('Informe ao menos uma especialidade.');
        }

        // Validar se todas as especialidades existem
        foreach ($data->specialtyIds as $specialty_id) {
            if (!Str::isUuid($specialty_id) || !Specialty::find($specialty_id)) {
                throw new SpecialtyNotFoundException('Especialidade não encontrada.');
            }
        }

        // Validar se CRM já existe
        if (Doctor::where('crm', $data->crm)->exists()) {
            throw new DuplicateCrmException('Médico com este CRM já existe.');
        }

        $doctor = Doctor::create([
            'name' => $data->name,
            'crm' => $data->crm,
            'email' => $data->email,
            'phone' => $data->phone,
            'bio' => $data->bio,
            'status' => DoctorStatus::ACTIVE,
        ]);

        $doctor->specialties()->attach($data->specialtyIds);

        event(new DoctorCreated($doctor));

        return $doctor->load('specialties');
    }
}
