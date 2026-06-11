<?php

namespace App\Domain\Price\Actions;

use App\Domain\Price\Models\Price;
use App\Domain\Price\Exceptions\PriceNotFoundException;

class DeletePriceAction
{
    public function execute(string $priceId): bool
    {
        $price = Price::find($priceId);

        if (!$price) {
            throw new PriceNotFoundException();
        }

        return (bool) $price->delete();
    }
}
