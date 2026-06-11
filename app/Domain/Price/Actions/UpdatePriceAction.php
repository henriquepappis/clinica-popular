<?php

namespace App\Domain\Price\Actions;

use App\Domain\Price\Models\Price;
use App\Domain\Price\Events\PriceUpdated;
use App\Domain\Price\Exceptions\PriceNotFoundException;
use App\Domain\Price\Exceptions\InvalidPriceException;

class UpdatePriceAction
{
    public function execute(string $priceId, float $value): Price
    {
        $price = Price::find($priceId);

        if (!$price) {
            throw new PriceNotFoundException();
        }

        if ($value <= 0) {
            throw new InvalidPriceException('O valor do preço deve ser maior que zero.');
        }

        $price->update(['value' => $value]);

        event(new PriceUpdated($price));

        return $price->fresh();
    }
}
