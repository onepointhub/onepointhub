<?php

namespace App\Modules\Projects\Enums;

enum ProjectType: string
{
    case Fixed = 'fixed';
    case Hourly = 'hourly';
    case Retainer = 'retainer';
}
