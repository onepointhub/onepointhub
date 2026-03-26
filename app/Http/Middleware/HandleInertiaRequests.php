<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'notifications' => function () use ($request): array {
                $user = $request->user();

                if (! $user) {
                    return ['unread_count' => 0, 'recent' => []];
                }

                return [
                    'unread_count' => $user->unreadNotifications()->count(),
                    'recent' => $user->notifications()->take(10)->get()->map(fn ($n) => [
                        'id' => $n->id,
                        'type' => $n->data['type'] ?? null,
                        'message' => $n->data['message'] ?? null,
                        'read_at' => $n->read_at?->toISOString(),
                        /** @phpstan-ignore-next-line */
                        'created_at' => $n->created_at->toISOString(),
                    ]),
                ];
            },
        ];
    }
}
