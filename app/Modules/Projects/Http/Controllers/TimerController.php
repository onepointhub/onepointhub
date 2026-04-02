<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Projects\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

class TimerController extends Controller
{
    /**
     * @throws Throwable
     */
    public function start(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('update-project');

        /** @var User $user */
        $user = $request->user();

        $entry = DB::transaction(function () use ($project, $user) {
            // Lock running entries for this user to prevent race conditions
            $project->timeEntries()
                ->where('user_id', $user->id)
                ->whereNull('ended_at')
                ->lockForUpdate()
                ->each(fn ($e) => $e->update([
                    'ended_at' => now(),
                    'duration_minutes' => (int) ($e->started_at->diffInMinutes(now())),
                ]));

            return $project->timeEntries()->create([
                'user_id' => $user->id,
                'started_at' => now(),
                'billable' => true,
            ]);
        });

        return response()->json(['entry_id' => $entry->id, 'started_at' => $entry->started_at]);
    }

    public function stop(Request $request, Project $project): JsonResponse
    {
        Gate::authorize('update-project');

        /** @var User $user */
        $user = $request->user();

        $entry = $project->timeEntries()
            ->where('user_id', $user->id)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        $entry?->update([
            'ended_at' => now(),
            'duration_minutes' => (int) ($entry->started_at->diffInMinutes(now())),
        ]);

        return response()->json(['stopped' => (bool) $entry]);
    }
}
