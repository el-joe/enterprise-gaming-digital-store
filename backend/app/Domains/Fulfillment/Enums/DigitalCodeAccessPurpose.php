<?php

namespace App\Domains\Fulfillment\Enums;

enum DigitalCodeAccessPurpose: string
{
    case CustomerReveal = 'customer_reveal';
    case SupportVerification = 'support_verification';
    case ManualDelivery = 'manual_delivery';
}
