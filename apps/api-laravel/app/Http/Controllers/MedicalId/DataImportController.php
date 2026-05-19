<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ImportJob;
use App\Modules\DataImport\Services\ImportMappingService;
use App\Modules\DataImport\Services\ImportRollbackService;
use App\Modules\DataImport\Services\ImportService;
use App\Modules\DataImport\Services\ImportValidationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class DataImportController extends Controller
{
    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    // ── History / Index ───────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = ImportJob::where('facility_id', $this->demoFacilityId())
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('import_type')) {
            $query->where('import_type', $request->import_type);
        }

        $jobs       = $query->limit(100)->get();
        $importTypes = ImportService::IMPORT_TYPES;

        return view('portals.staff.data_import.index', compact('jobs', 'importTypes'));
    }

    // ── Upload Wizard Step 1 ──────────────────────────────────────

    public function create(): View
    {
        $importTypes = ImportService::IMPORT_TYPES;
        return view('portals.staff.data_import.upload', compact('importTypes'));
    }

    public function store(Request $request, ImportService $svc): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'import_type' => 'required|string',
            'file'        => 'required|file|mimes:csv,xlsx,xls|max:25600',
        ]);

        try {
            $job = $svc->uploadFile(
                $request->file('file'),
                $request->import_type,
                $this->demoFacilityId(),
                $this->demoActorId()
            );

            return redirect()->route('portals.staff.data_import.mapping', $job->id)
                ->with('success', 'File uploaded. Review the column mapping below.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Mapping Step 2 ────────────────────────────────────────────

    public function mapping(string $id, ImportMappingService $mappingSvc): View
    {
        $job          = ImportJob::findOrFail($id);
        $importTypes  = ImportService::IMPORT_TYPES;
        $systemFields = $mappingSvc->systemFields($job->import_type);
        $saved        = $mappingSvc->savedMappings($this->demoFacilityId(), $job->import_type);
        $suggested    = $job->mapping ?? (new ImportService())->suggestMapping($job->detected_headers ?? [], $job->import_type) ?? [];

        return view('portals.staff.data_import.mapping', compact(
            'job', 'importTypes', 'systemFields', 'saved', 'suggested'
        ));
    }

    public function mappingStore(Request $request, string $id, ImportMappingService $mappingSvc): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'mapping'   => 'required|array',
            'mapping.*' => 'nullable|string',
            'save_as'   => 'nullable|string|max:80',
        ]);

        try {
            $job     = ImportJob::findOrFail($id);
            $mapping = array_filter($request->mapping, fn($v) => !empty($v));

            $mappingSvc->applyMapping($job, $mapping, $this->demoActorId(), $request->save_as ?: null);

            return redirect()->route('portals.staff.data_import.validate', $id)
                ->with('success', 'Mapping saved. Running validation…');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Validation Step 3 ─────────────────────────────────────────

    public function validate(string $id, ImportValidationService $validationSvc): \Illuminate\Http\RedirectResponse
    {
        try {
            $job = ImportJob::findOrFail($id);
            $validationSvc->validate($job);

            return redirect()->route('portals.staff.data_import.preview', $id)
                ->with('success', 'Validation complete.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ── Preview Step 4 ────────────────────────────────────────────

    public function preview(string $id): View
    {
        $job    = ImportJob::with(['rowErrors' => fn($q) => $q->orderBy('row_number')->limit(200)])->findOrFail($id);
        $importTypes = ImportService::IMPORT_TYPES;

        return view('portals.staff.data_import.preview', compact('job', 'importTypes'));
    }

    // ── Approve Step 5 ────────────────────────────────────────────

    public function approve(Request $request, string $id, ImportService $svc): \Illuminate\Http\RedirectResponse
    {
        try {
            $job = ImportJob::findOrFail($id);

            if (!$job->canBeApproved()) {
                throw new \RuntimeException("Job cannot be approved in status: {$job->status}");
            }

            $job->forceFill([
                'status'      => 'approved_for_import',
                'approved_by' => $this->demoActorId(),
                'approved_at' => now(),
            ])->save();

            $svc->audit($job, 'approved', $this->demoActorId());

            // In a production system, a queued job would be dispatched here.
            // For portal demo: simulate immediate completion.
            $job->forceFill([
                'status'                => 'completed',
                'imported_rows'         => $job->valid_rows,
                'import_started_at'     => now(),
                'import_completed_at'   => now(),
            ])->save();

            $svc->audit($job, 'completed', $this->demoActorId(), [
                'imported' => $job->valid_rows,
                'note'     => 'Portal demo: simulated import completion.',
            ]);

            return redirect()->route('portals.staff.data_import.index')
                ->with('success', "Import approved. {$job->valid_rows} valid records processed.");
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ── Rollback ──────────────────────────────────────────────────

    public function rollback(Request $request, string $id, ImportRollbackService $rollbackSvc): \Illuminate\Http\RedirectResponse
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        try {
            $job = ImportJob::findOrFail($id);
            $rollbackSvc->rollback($job, $this->demoActorId(), $request->reason);

            return redirect()->route('portals.staff.data_import.index')
                ->with('success', 'Import rolled back.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ── Cancel ────────────────────────────────────────────────────

    public function cancel(string $id, ImportService $svc): \Illuminate\Http\RedirectResponse
    {
        try {
            $job = ImportJob::findOrFail($id);
            $svc->cancelJob($job, $this->demoActorId());

            return redirect()->route('portals.staff.data_import.index')
                ->with('success', 'Import cancelled.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ── Audit Log ─────────────────────────────────────────────────

    public function auditLog(string $id): View
    {
        $job    = ImportJob::with('auditEvents')->findOrFail($id);
        $importTypes = ImportService::IMPORT_TYPES;

        return view('portals.staff.data_import.audit', compact('job', 'importTypes'));
    }
}
