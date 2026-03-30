<?php

namespace App\Enums;

enum ClientStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
