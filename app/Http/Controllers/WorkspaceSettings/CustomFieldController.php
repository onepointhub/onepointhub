<?php

namespace App\Http\Controllers\WorkspaceSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkspaceSettings\StoreCustomFieldRequest;
use App\Modules\Clients\Enums\CustomFieldType;
use App\Modules\Clients\Models\CustomFieldDefinition;
use App\Modules\Core\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CustomFieldController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('manage-workspace');

        $fields = CustomFieldDefinition::orderBy('sort_order')
            ->get()
            ->map(fn (CustomFieldDefinition $field) => [
                'id' => $field->id,
                'label' => $field->label,
                'type' => $field->type->value,
                'options' => $field->options,
            ]);

        return Inertia::render('workspace/settings/CustomFields', [
            'fields' => $fields,
            'types' => array_column(CustomFieldType::cases(), 'value'),
        ]);
    }

    public function store(StoreCustomFieldRequest $request): RedirectResponse
    {
        $workspace = app(Workspace::class);

        /** @var int $maxOrder */
        $maxOrder = CustomFieldDefinition::max('sort_order') ?? -1;

        CustomFieldDefinition::create([
            ...$request->validated(),
            'workspace_id' => $workspace->id,
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()->route('workspace.custom-fields.index');
    }

    public function destroy(CustomFieldDefinition $customField): RedirectResponse
    {
        Gate::authorize('manage-workspace');

        $customField->delete();

        return redirect()->route('workspace.custom-fields.index');
    }
}
