<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Modules\DataImport\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Executes an approved data import job by reading the validated file,
 * mapping columns, and inserting rows into the appropriate target table.
 *
 * Each import type maps to a database table; rows are upserted in chunks
 * of 200 to stay within memory bounds for large files.
 */
class ExecuteImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries  = 2;
    public int $timeout = 600;

    // Map import_type → target table (only insertable types)
    private const TABLE_MAP = [
        'pharmacy_stock'      => 'drug_stock_items',
        'medicine_catalog'    => 'drugs',
        'lab_test_catalog'    => 'lab_tests',
        'insurance_providers' => 'insurance_providers',
        'price_lists'         => 'service_prices',
        'inventory_items'     => 'inventory_items',
    ];

    public function __construct(
        public readonly string $importJobId,
        public readonly string $actorId,
    ) {}

    public function handle(ImportService $svc): void
    {
        $job = ImportJob::findOrFail($this->importJobId);

        if ($job->status !== 'approved_for_import') {
            Log::warning('ExecuteImportJob: unexpected status', ['id' => $this->importJobId, 'status' => $job->status]);
            return;
        }

        $job->forceFill(['status' => 'importing', 'import_started_at' => now()])->save();
        $svc->audit($job, 'import_started', $this->actorId);

        try {
            $importedCount = $this->executeImport($job, $svc);

            $job->forceFill([
                'status'              => 'completed',
                'imported_rows'       => $importedCount,
                'import_completed_at' => now(),
            ])->save();

            $svc->audit($job, 'completed', $this->actorId, ['imported' => $importedCount]);
        } catch (\Throwable $e) {
            $job->forceFill(['status' => 'failed'])->save();
            $svc->audit($job, 'failed', $this->actorId, ['error' => $e->getMessage()]);
            Log::error('ExecuteImportJob failed', ['id' => $this->importJobId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function executeImport(ImportJob $job, ImportService $svc): int
    {
        $table = self::TABLE_MAP[$job->import_type] ?? null;

        // Patient / staff / appointment imports touch complex domain models —
        // they need event dispatching, health-ID generation, etc. For now mark
        // as a manual review type so the UI can guide staff through the model-
        // specific import path.
        if (!$table) {
            $job->forceFill(['status' => 'requires_manual_review'])->save();
            $svc->audit($job, 'deferred_to_manual', $this->actorId, [
                'reason' => "Import type '{$job->import_type}' requires domain-specific processing.",
            ]);
            return 0;
        }

        $rows    = $svc->readRows($job->stored_path, $job->file_extension, $job->detected_headers ?? []);
        $mapping = $job->mapping ?? [];
        $count   = 0;

        // Process in chunks of 200 rows
        foreach (array_chunk($rows, 200) as $chunk) {
            $inserts = [];
            foreach ($chunk as $raw) {
                $row = ['facility_id' => $job->facility_id, 'created_at' => now(), 'updated_at' => now()];
                foreach ($mapping as $csvCol => $dbCol) {
                    if ($dbCol && isset($raw[$csvCol])) {
                        $row[$dbCol] = $raw[$csvCol] ?: null;
                    }
                }
                $inserts[] = $row;
            }
            if (!empty($inserts)) {
                DB::table($table)->insertOrIgnore($inserts);
                $count += count($inserts);
            }
        }

        return $count;
    }
}
