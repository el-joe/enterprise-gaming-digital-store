<?php

namespace App\Domains\Orders\Exceptions;

use App\Domains\Shared\Exceptions\ApplicationException;

class PriceChanged extends ApplicationException
{
    public function __construct(public readonly string $currentTotal, public readonly string $currency)
    {
        $message = "The order total is now {$currentTotal} {$currency}. Please review it before paying.";

        parent::__construct($message, 'price_changed', 422, ['expected_total' => [$message]]);
    }

    public function localizedMessage(): string
    {
        return __('The order total is now :total :currency. Please review it before paying.', ['total' => $this->currentTotal, 'currency' => $this->currency]);
    }

    public function localizedErrors(): array
    {
        return ['expected_total' => [$this->localizedMessage()]];
    }
}
