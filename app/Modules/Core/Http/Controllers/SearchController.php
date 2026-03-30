<?php

namespace App\Modules\Core\Http\Controllers;

use App\Modules\Core\Models\Workspace;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']])['q'];

        $workspace = app(Workspace::class);

        $members = $workspace->members()
            ->where(function ($q) use ($query): void {
                $q->where('users.name', 'LIKE', "%$query%")
                    ->orWhere('users.email', 'LIKE', "%$query%");
            })
            ->limit(5)
            ->get()
            ->map(fn ($member) => [
                'type' => 'member',
                'label' => $member->name,
                'sublabel' => $member->email,
                'avatar' => $member->avatar,
                'href' => null,
            ]);

        return response()->json([
            'results' => $members->values(),
            'query' => $query,
        ]);
    }
}
