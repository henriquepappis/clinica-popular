<?php

namespace App\Domain\Price\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Price\Models\Price;

class PriceCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Price $price
    ) {}
}
