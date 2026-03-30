<?php

namespace App\Modules\Core\Enums;

enum WorkspaceRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Member = 'member';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Owner => __('Owner'),
            self::Admin => __('Admin'),
            self::Member => __('Member'),
            self::Client => __('Client'),
        };
    }

    /**
     * Roles that can access internal (non-portal) routes
     *
     * @return array<string>
     */
    public static function internal(): array
    {
        return [self::Owner->value, self::Admin->value, self::Member->value];
    }
}
