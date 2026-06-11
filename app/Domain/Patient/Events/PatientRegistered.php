<?php

namespace App\Domain\Patient\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Patient\Models\Patient;

class PatientRegistered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Patient $patient
    ) {}
}