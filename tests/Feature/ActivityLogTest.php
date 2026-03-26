<?php

use App\Models\ActivityLog;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can be created and has no updated_at column', function () {
    [$user, $workspace] = workspaceWithUser('owner');

    $log = ActivityLog::create([
        'workspace_id' => $workspace->id,
        'user_id' => $user->id,
        'event' => 'created',
        'subject_type' => Workspace::class,
        'subject_id' => (string) $workspace->id,
        'properties' => null,
    ]);

    expect($log->id)->toBeInt()
        ->and($log->event)->toBe('created')
        ->and($log->updated_at)->toBeNull();
});
