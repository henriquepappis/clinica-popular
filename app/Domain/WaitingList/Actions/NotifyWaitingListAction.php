<?php

namespace App\Domain\WaitingList\Actions;

use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\WaitingList\Events\PatientNotifiedFromWaitingList;
use App\Domain\WaitingList\Exceptions\WaitingListNotFoundException;
use Carbon\Carbon;

class NotifyWaitingListAction
{
    public function execute(string $waitingListId): WaitingList
    {
        $waitingList = WaitingList::find($waitingListId);

        if (!$waitingList) {
            throw new WaitingListNotFoundException();
        }

        $waitingList->update([
            'status' => 'notified',
            'notified_at' => Carbon::now(),
        ]);

        event(new PatientNotifiedFromWaitingList($waitingList));

        return $waitingList;
    }
}
