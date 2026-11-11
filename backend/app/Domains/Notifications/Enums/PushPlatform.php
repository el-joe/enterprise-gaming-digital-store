<?php

namespace App\Domains\Notifications\Enums;

/**
 * The platform of an app device that registered a push token (M-A18). Both go through FCM; iOS
 * is relayed by FCM to Apple's push service.
 */
enum PushPlatform: string
{
    case Android = 'android';
    case Ios = 'ios';
}
