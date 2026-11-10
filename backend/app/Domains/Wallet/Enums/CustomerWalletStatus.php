<?php

namespace App\Domains\Wallet\Enums;

enum CustomerWalletStatus: string
{
    case Active = 'active';
    case Frozen = 'frozen';
}
