<?php

namespace App\Domains\Wallet\Enums;

enum WalletTransactionType: string
{
    case CreditRefund = 'credit_refund';
    case CreditAdmin = 'credit_admin';
    case DebitPurchase = 'debit_purchase';
    case DebitAdmin = 'debit_admin';
    case Reversal = 'reversal';
}
