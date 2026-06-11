<?php

namespace App\Domain\Shift\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Shift\Models\Shift;

class ShiftCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Shift $shift
    ) {}
}