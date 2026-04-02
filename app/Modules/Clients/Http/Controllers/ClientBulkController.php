<?php

namespace App\Modules\Clients\Http\Controllers;

use App\Modules\Clients\Enums\ClientStatus;
use App\Modules\Clients\Models\Client;
use App\Modules\Core\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClientBulkController extends Controller
{
    public function archive(Request $request): RedirectResponse
    {
        Gate::authorize('delete-client');

        $ids = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']])['ids'];

        // whereIn is workspace-scoped by the global scope — foreign IDs are silently ignored.
        Client::whereIn('id', $ids)->update(['status' => ClientStatus::Archived->value]);

        return redirect()->route('clients.index');
    }
}
