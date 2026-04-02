<?php

namespace App\Modules\Billing\Enum;

enum ExpenseCategory: string
{
    case Software = 'software';
    case Hardware = 'hardware';
    case Travel = 'travel';
    case Meals = 'meals';
    case Advertising = 'advertising';
    case Contractor = 'contractor';
    case Office = 'office';
    case Other = 'other';
}
