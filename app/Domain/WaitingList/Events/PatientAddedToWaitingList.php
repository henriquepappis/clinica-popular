<?php

namespace App\Domain\WaitingList\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\WaitingList\Models\WaitingList;

class PatientAddedToWaitingList
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WaitingList $waitingList
    ) {}
}
