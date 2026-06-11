<?php

namespace App\Domain\WaitingList\Actions;

use App\Domain\WaitingList\Models\WaitingList;
use App\Domain\WaitingList\Events\PatientRemovedFromWaitingList;
use App\Domain\WaitingList\Exceptions\WaitingListNotFoundException;

class RemoveFromWaitingListAction
{
    public function execute(string $waitingListId, string $reason = null): WaitingList
    {
        $waitingList = WaitingList::find($waitingListId);

        if (!$waitingList) {
            throw new WaitingListNotFoundException();
        }

        $waitingList->update([
            'status' => 'cancelled',
            'reason' => $reason ?? $waitingList->reason,
        ]);

        event(new PatientRemovedFromWaitingList($waitingList));

        return $waitingList;
    }
}
