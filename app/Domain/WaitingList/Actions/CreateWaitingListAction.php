<?php

namespace App\Domain\WaitingList\Actions;

use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\WaitingList\DataTransferObjects\WaitingListData;
use App\Domain\WaitingList\Events\PatientAddedToWaitingList;
use App\Domain\WaitingList\Exceptions\PatientAlreadyInWaitingListException;
use App\Domain\Patient\Models\Patient;
use App\Domain\Specialty\Models\Specialty;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateWaitingListAction
{
    public function execute(WaitingListData $data): WaitingList
    {
        // Validar se paciente existe
        if (!Str::isUuid($data->patientId) || !Patient::find($data->patientId)) {
            throw new \Exception('Paciente não encontrado.');
        }

        // Validar se especialidade existe
        if (!Str::isUuid($data->specialtyId) || !Specialty::find($data->specialtyId)) {
            throw new \Exception('Especialidade não encontrada.');
        }

        // Verificar se paciente já está na fila para esta especialidade
        $existing = WaitingList::where('patient_id', $data->patientId)
            ->where('specialty_id', $data->specialtyId)
            ->where('status', 'waiting')
            ->exists();

        if ($existing) {
            throw new PatientAlreadyInWaitingListException(
                'Paciente já está na fila de espera para esta especialidade.'
            );
        }

        $waitingList = WaitingList::create([
            'patient_id' => $data->patientId,
            'specialty_id' => $data->specialtyId,
            'priority' => $data->priority,
            'reason' => $data->reason,
            'status' => 'waiting',
            'added_at' => Carbon::now(),
        ]);

        event(new PatientAddedToWaitingList($waitingList));

        return $waitingList;
    }
}
