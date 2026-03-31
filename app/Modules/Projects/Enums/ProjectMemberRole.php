<?php

namespace App\Modules\Projects\Enums;

enum ProjectMemberRole: string
{
    case Lead = 'lead';
    case Member = 'member';
}
