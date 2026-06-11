<?php

namespace App\Domain\Specialty\Actions;

use App\Domain\Specialty\Models\Specialty;
use App\Domain\Specialty\DataTransferObjects\SpecialtyData;
use App\Domain\Specialty\Events\SpecialtyCreated;
use App\Domain\Specialty\Enums\SpecialtyStatus;

class CreateSpecialtyAction
{
    public function execute(SpecialtyData $data): Specialty
    {
        $specialty = Specialty::create([
            'name' => $data->name,
            'description' => $data->description,
            'status' => SpecialtyStatus::ACTIVE,
        ]);

        event(new SpecialtyCreated($specialty));

        return $specialty;
    }
}