<?php

namespace App\Domains\Catalog\Enums;

enum DeliveryType: string
{
    case InstantCode = 'instant_code';
    case DirectTopup = 'direct_topup';
    case AccountRecharge = 'account_recharge';
    case ApiDelivery = 'api_delivery';
    case Manual = 'manual';
    case SubscriptionActivation = 'subscription_activation';
}
