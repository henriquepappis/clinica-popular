<?php

namespace App\Domain\WaitingList\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\WaitingList\Models\WaitingList;

class PatientRemovedFromWaitingList
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public WaitingList $waitingList
    ) {}
}
