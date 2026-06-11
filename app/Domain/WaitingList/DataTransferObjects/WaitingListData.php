<?php

namespace App\Domain\WaitingList\DataTransferObjects;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\UUID;
use Spatie\LaravelData\Attributes\Validation\Between;

class WaitingListData extends Data
{
    public function __construct(
        #[Required, UUID]
        public string $patientId,

        #[Required, UUID]
        public string $specialtyId,

        #[Required, Between(1, 3)]
        public int $priority = 1,

        public ?string $reason = null,
    ) {}
}
