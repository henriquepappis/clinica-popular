<?php

namespace App\Domain\Specialty\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Specialty\Models\Specialty;

class SpecialtyCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Specialty $specialty
    ) {}
}