<?php

namespace App\Domains\Cms\Enums;

enum NewsletterSubscriberStatus: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
}
