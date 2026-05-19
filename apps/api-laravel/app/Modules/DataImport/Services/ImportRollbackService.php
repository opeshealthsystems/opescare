<?php

namespace App\Modules\DataImport\Services;

use App\Models\ImportJob;
use RuntimeException;

class ImportRollbackService
{
    public function __construct(
        private ImportService $importService,
    ) {}

    /**
     * Roll back a completed import batch.
     * Since this is a wizard-style portal (no actual record creation yet),
     * rollback marks the job as rolled_back and logs the action.
     * When real import execution is wired, this would delete/revert
     * the created records via import_audit_events.
     */
    public function rollback(ImportJob $job, string $actorId, ?string $reason = null): ImportJob
    {
        if (!$job->canBeRolledBack()) {
            throw new RuntimeException("Import job cannot be rolled back from status: {$job->status}");
        }

        $job->forceFill(['status' => 'rolled_back'])->save();

        $this->importService->audit($job, 'rolled_back', $actorId, [
            'reason'          => $reason,
            'previously_imported' => $job->imported_rows,
        ]);

        return $job->fresh();
    }
}
