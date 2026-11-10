<?php

namespace App\Http\Resources\Account;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\Models\CustomerWallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CustomerWallet
 */
class WalletResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'currency' => $this->currency,
            'balance' => app(CurrencyConverter::class)->present(Money::of($this->balance, $this->currency)),
            'status' => $this->status->value,
            'top_up_available' => false,
        ];
    }
}
