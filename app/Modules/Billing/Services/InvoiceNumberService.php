<?php

namespace App\Modules\Billing\Services;

use App\Modules\Core\Models\Workspace;
use Illuminate\Support\Facades\DB;
use Throwable;

class InvoiceNumberService
{
    /**
     * Generate the next invoice number for the workspace.
     * Increments and persists the sequence counter atomically.
     *
     * @throws Throwable
     */
    public function nextNumber(Workspace $workspace): string
    {
        return DB::transaction(function () use ($workspace) {
            /** @var Workspace $locked */
            $locked = Workspace::lockForUpdate()->findOrFail($workspace->id);

            [$settings, $currentYear, $sequence] = $this->getSequence($locked);

            $sequence++;

            $settings['invoice_sequence'] = $sequence;
            $settings['invoice_last_year'] = $currentYear;

            $locked->settings = $settings;
            $locked->saveQuietly();

            // Refresh the workspace in memory so callers see the new sequence.
            $workspace->settings = $locked->settings;

            /** @var string $prefix */
            $prefix = $settings['invoice_prefix'] ?? 'INV';

            return $this->formatNumber($prefix, $currentYear, $sequence);
        });
    }

    /**
     * Return a preview of the next invoice number without incrementing the counter.
     */
    public function previewNext(Workspace $workspace): string
    {
        [$settings, $currentYear, $sequence] = $this->getSequence($workspace);

        /** @var string $prefix */
        $prefix = $settings['invoice_prefix'] ?? 'INV';

        return $this->formatNumber($prefix, $currentYear, $sequence + 1);
    }

    protected function formatNumber(string $prefix, int $year, int $sequence): string
    {
        return sprintf('%s-%d-%04d', $prefix, $year, $sequence);
    }

    /**
     * @return array{0: array<string, mixed>, 1: int, 2: int}
     */
    protected function getSequence(Workspace $workspace): array
    {
        /** @var array<string, mixed> $settings */
        $settings = $workspace->settings ?? [];
        $currentYear = (int) date('Y');
        $lastYear = (is_int($settings['invoice_last_year'] ?? null) || is_string($settings['invoice_last_year'] ?? null)) ? (int) $settings['invoice_last_year'] : $currentYear;
        $yearReset = (bool) ($settings['invoice_year_reset'] ?? false);

        $sequence = (is_int($settings['invoice_sequence'] ?? null) || is_string($settings['invoice_sequence'] ?? null)) ? (int) $settings['invoice_sequence'] : 0;

        if ($yearReset && $lastYear < $currentYear) {
            $sequence = 0;
        }

        return [$settings, $currentYear, $sequence];
    }
}
