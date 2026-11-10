<?php

namespace App\Domains\Payments\Services;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Customers\Models\CustomerProfile;
use App\Domains\Payments\DTOs\EligibilityContext;
use App\Domains\Payments\DTOs\PaymentCurrencyQuery;

class EligibilityContextFactory
{
    public function fromQuery(PaymentCurrencyQuery $query): EligibilityContext
    {
        return new EligibilityContext(
            displayCurrency: strtoupper($query->displayCurrency),
            totalBase: $query->totalBase,
            countryCode: strtoupper($query->countryCode),
            ipCountry: $query->ipCountry === null ? null : strtoupper($query->ipCountry),
            isGuest: $query->isGuest,
            riskLevel: $this->riskLevel($query->userId),
            productIds: $query->productIds,
            userId: $query->isGuest ? null : $query->userId,
        );
    }

    /**
     * A guest has no history and counts as low risk here; the risk engine scores the order
     * after payment either way.
     */
    private function riskLevel(?int $userId): CustomerRiskLevel
    {
        if ($userId === null) {
            return CustomerRiskLevel::Low;
        }

        return CustomerRiskLevel::tryFrom((string) CustomerProfile::query()->where('user_id', $userId)->value('risk_level')) ?? CustomerRiskLevel::Low;
    }
}
