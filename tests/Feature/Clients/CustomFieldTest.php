<?php

use App\Enums\CustomFieldType;
use App\Models\Client;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;

it('renders the custom fields settings page', function () {
    actingAsWorkspaceMember('admin');

    $this->get(route('workspace.custom-fields.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('workspace/settings/CustomFields'));
});

it('stores a new text custom field definition', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('workspace.custom-fields.store'), [
        'label' => 'Industry',
        'type' => CustomFieldType::Text->value,
    ])->assertRedirect();

    $this->assertDatabaseHas('custom_field_definitions', ['label' => 'Industry']);
});

it('stores a select custom field with options', function () {
    actingAsWorkspaceMember('admin');

    $this->post(route('workspace.custom-fields.store'), [
        'label' => 'Tier',
        'type' => CustomFieldType::Select->value,
        'options' => ['Bronze', 'Silver', 'Gold'],
    ])->assertRedirect();

    $definition = CustomFieldDefinition::where('label', 'Tier')->first();
    expect($definition->options)->toBe(['Bronze', 'Silver', 'Gold']);
});

it('requires admin permission to manage custom fields', function () {
    actingAsWorkspaceMember('member');

    $this->post(route('workspace.custom-fields.store'), [
        'label' => 'Test',
        'type' => CustomFieldType::Text->value,
    ])->assertForbidden();
});

it('deletes a custom field definition', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $definition = CustomFieldDefinition::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $this->delete(route('workspace.custom-fields.destroy', $definition))
        ->assertRedirect();

    $this->assertDatabaseMissing('custom_field_definitions', ['id' => $definition->id]);
});

it('saves a custom field value for a client', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $definition = CustomFieldDefinition::factory()->create([
        'workspace_id' => $workspace->id,
        'type' => CustomFieldType::Text->value,
    ]);
    $client = Client::factory()->create();

    $this->patch(route('clients.update', $client), [
        'name' => $client->name,
        'type' => $client->type->value,
        'status' => $client->status->value,
        'custom_fields' => [
            $definition->id => 'Manufacturing',
        ],
    ])->assertRedirect();

    expect(
        CustomFieldValue::where('custom_field_definition_id', $definition->id)
            ->where('model_id', $client->id)
            ->value('value')
    )->toBe('Manufacturing');
});

it('returns custom field definitions and values on the edit page', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $definition = CustomFieldDefinition::factory()->create([
        'workspace_id' => $workspace->id,
    ]);

    $client = Client::factory()->create();
    CustomFieldValue::factory()->create([
        'custom_field_definition_id' => $definition->id,
        'model_type' => Client::class,
        'model_id' => $client->id,
        'value' => 'Test Value',
    ]);

    $this->get(route('clients.edit', $client))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('customFields')
            ->has('customFieldValues')
        );
});

it('includes custom field values in csv export', function () {
    [$user, $workspace] = actingAsWorkspaceMember('admin');

    $definition = CustomFieldDefinition::factory()->create([
        'workspace_id' => $workspace->id,
        'label' => 'Industry',
    ]);

    $client = Client::factory()->create();
    CustomFieldValue::factory()->create([
        'custom_field_definition_id' => $definition->id,
        'model_type' => Client::class,
        'model_id' => $client->id,
        'value' => 'Tech',
    ]);

    $response = $this->get(route('clients.export'));
    $content = $response->streamedContent();

    expect($content)->toContain('Industry')
        ->and($content)->toContain('Tech');
});
