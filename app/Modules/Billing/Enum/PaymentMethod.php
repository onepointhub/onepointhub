<?php

namespace App\Modules\Billing\Enum;

enum PaymentMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Cheque = 'cheque';
    case CreditCard = 'credit_card';
    case Other = 'other';
}
