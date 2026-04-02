<?php

use App\Modules\Billing\Services\InvoiceNumberService;
use App\Modules\Core\Models\Workspace;

it('generates the first invoice number with default prefix', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = [];
    $workspace->save();

    $service = new InvoiceNumberService;
    $number = $service->nextNumber($workspace);

    $year = date('Y');
    expect($number)->toBe("INV-$year-0001");
});

it('increments the sequence on each call', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = [];
    $workspace->save();

    $service = new InvoiceNumberService;
    $first = $service->nextNumber($workspace);
    $second = $service->nextNumber($workspace);

    $year = date('Y');
    expect($first)->toBe("INV-$year-0001")
        ->and($second)->toBe("INV-$year-0002");
});

it('uses a custom prefix from workspace settings', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = ['invoice_prefix' => 'ACME'];
    $workspace->save();

    $service = new InvoiceNumberService;
    $number = $service->nextNumber($workspace);

    $year = date('Y');
    expect($number)->toBe("ACME-$year-0001");
});

it('resets the sequence when the year changes and year_reset is enabled', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = [
        'invoice_sequence' => 5,
        'invoice_year_reset' => true,
        'invoice_last_year' => (int) date('Y') - 1,
    ];
    $workspace->save();

    $service = new InvoiceNumberService;
    $number = $service->nextNumber($workspace);

    $year = date('Y');
    expect($number)->toBe("INV-$year-0001");
});

it('does not reset sequence when year_reset is disabled', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = [
        'invoice_sequence' => 10,
        'invoice_year_reset' => false,
        'invoice_last_year' => (int) date('Y') - 1,
    ];
    $workspace->save();

    $service = new InvoiceNumberService;
    $number = $service->nextNumber($workspace);

    $year = date('Y');
    expect($number)->toBe("INV-$year-0011");
});

it('previews the next invoice number without consuming it', function () {
    actingAsWorkspaceMember('admin');

    $workspace = app(Workspace::class);
    $workspace->settings = ['invoice_sequence' => 3];
    $workspace->save();

    $service = new InvoiceNumberService;
    $preview = $service->previewNext($workspace);
    $preview2 = $service->previewNext($workspace);

    $year = date('Y');
    expect($preview)->toBe("INV-$year-0004")
        ->and($preview2)->toBe("INV-$year-0004");
    // unchanged — preview does not increment
});
