<?php

namespace App\Domain\Doctor\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class DoctorSpecialty extends Pivot
{
    use HasUuids;

    protected $table = 'doctor_specialties';
}
