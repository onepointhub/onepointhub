<?php

namespace App\Modules\Projects\Http\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Models\User;
use App\Modules\Projects\Http\Requests\StoreTimeEntryRequest;
use App\Modules\Projects\Models\Project;
use App\Modules\Projects\Models\TimeEntry;
use DateMalformedStringException;
use DateTime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class TimeEntryController extends Controller
{
    /**
     * @throws DateMalformedStringException
     */
    public function store(StoreTimeEntryRequest $request, Project $project): RedirectResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = $request->user();
        $data['user_id'] = $user->id;
        /** @var string $started_at */
        $started_at = $data['started_at'];
        /** @var string $ended_at */
        $ended_at = $data['ended_at'];

        $started = new DateTime($started_at);
        $ended = new DateTime($ended_at);
        $data['duration_minutes'] = (int) (($ended->getTimestamp() - $started->getTimestamp()) / 60);

        $project->timeEntries()->create($data);

        return redirect()->back();
    }

    public function destroy(Project $project, TimeEntry $entry): RedirectResponse
    {
        Gate::authorize('update-project');
        abort_unless($entry->project_id === $project->id, 404);
        abort_if($entry->isInvoiced(), 403);

        $entry->delete();

        return redirect()->back();
    }
}
