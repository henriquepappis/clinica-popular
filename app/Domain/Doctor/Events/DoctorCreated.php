<?php

namespace App\Domain\Doctor\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Doctor\Models\Doctor;

class DoctorCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Doctor $doctor
    ) {}
}