<?php

namespace App\Domains\Notifications\Enums;

enum NotificationPreferenceCategory: string
{
    case Orders = 'orders';
    case Support = 'support';
    case Marketing = 'marketing';
    case Security = 'security';
}
