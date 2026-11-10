<?php

namespace App\Domains\Notifications\Enums;

enum NotificationPreferenceChannel: string
{
    case Mail = 'mail';
    case Database = 'database';
}
