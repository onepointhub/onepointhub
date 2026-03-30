<?php

namespace App\Modules\Clients\Enums;

enum ClientType: string
{
    case Individual = 'individual';
    case Company = 'company';
}
