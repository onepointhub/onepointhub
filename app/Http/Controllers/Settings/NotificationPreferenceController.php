<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationPreferenceRequest;
use App\Modules\Core\Enums\NotificationType;
use App\Modules\Core\Models\NotificationPreference;
use App\Modules\Core\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationPreferenceController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $preferences = NotificationPreference::where('user_id', $user->id)
            ->get()
            ->keyBy('type')
            ->map(fn ($p) => ['email_enabled' => $p->email_enabled]);

        return Inertia::render('settings/Notifications', [
            'types' => array_column(NotificationType::cases(), 'value'),
            'preferences' => $preferences,
        ]);
    }

    public function update(UpdateNotificationPreferenceRequest $request, string $type): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        abort_unless(
            in_array($type, array_column(NotificationType::cases(), 'value'), strict: true),
            404
        );

        NotificationPreference::updateOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['email_enabled' => $request->boolean('email_enabled')]
        );

        return back();
    }
}
