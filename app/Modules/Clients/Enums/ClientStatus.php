<?php

namespace App\Modules\Clients\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
