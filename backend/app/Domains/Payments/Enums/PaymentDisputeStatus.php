<?php

namespace App\Domains\Payments\Enums;

enum PaymentDisputeStatus: string
{
    case Open = 'open';
    case Won = 'won';
    case Lost = 'lost';
    case Closed = 'closed';
}
