<?php

namespace App\Models;

use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'type', 'email_enabled'])]
class NotificationPreference extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
        ];
    }

    /**
     * Returns true if the user wants email for this notification type.
     * Defaults to true (opt-out model) when no preference row exists.
     */
    public static function emailEnabled(User $user, NotificationType $type): bool
    {
        return static::where('user_id', $user->id)
            ->where('type', $type->value)
            ->first()->email_enabled ?? true;
    }
}
