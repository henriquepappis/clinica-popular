<?php

namespace App\Domain\Shift\Actions;

use App\Domain\Shift\Models\Shift;
use App\Domain\Shift\DataTransferObjects\ShiftData;
use App\Domain\Shift\Events\ShiftCreated;
use App\Domain\Shift\Enums\ShiftStatus;

class CreateShiftAction
{
    public function execute(ShiftData $data): Shift
    {
        $shift = Shift::create([
            'name' => $data->name,
            'period' => $data->period,
            'start_time' => $data->startTime,
            'end_time' => $data->endTime,
            'max_appointments' => $data->maxAppointments,
            'status' => ShiftStatus::ACTIVE,
        ]);

        event(new ShiftCreated($shift));

        return $shift;
    }
}