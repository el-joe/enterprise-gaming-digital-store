<?php

namespace App\Domains\Customers\Enums;

enum CustomerConsentChannel: string
{
    case EmailMarketing = 'email_marketing';
    case SmsMarketing = 'sms_marketing';
    case Analytics = 'analytics';
}
