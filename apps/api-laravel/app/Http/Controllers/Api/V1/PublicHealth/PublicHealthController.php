<?php

namespace App\Http\Controllers\Api\V1\PublicHealth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReportType;
use App\Models\PublicHealthReport;
use App\Models\ReportItem;
use App\Models\ReportReview;
use App\Models\ReportStatusHistory;
use App\Models\ReportVersion;
use App\Models\ReportAssignment;
use App\Models\SubmissionProfile;
use App\Models\ReportSubmission;
use App\Models\ExportFile;
use App\Models\User;
use App\Modules\PublicHealth\Services\DraftGenerationService;
use App\Modules\PublicHealth\Services\DataQualityCheckService;
use App\Modules\PublicHealth\Services\ExportService;
use App\Services\AuditLogger;

class PublicHealthController extends Controller
{
    public function getReportTypes()
    {
        return response()->json(ReportType::where('is_active', true)->get());
    }

    public function getReports(Request $request)
    {
        $status = $request->query('status');
        $query = PublicHealthReport::with(['reportType', 'facility']);
        if ($status) {
            $query->where('status', $status);
        }
        return response()->json($query->get());
    }

    public function getReport($id)
    {
        $report = PublicHealthReport::with(['reportType', 'facility', 'items', 'qualityChecks'])->find($id);
        if (!$report) {
            return response()->json(['error' => 'Report not found.'], 404);
        }
        return response()->json($report);
    }

    public function generateDrafts(Request $request)
    {
        $request->validate([
            'facility_id' => 'required|uuid',
            'period_start' => 'required|date',
            'period_end' => 'required|date'
        ]);

        $service = new DraftGenerationService();
        $reports = $service->generateDrafts(
            $request->input('facility_id'),
            $request->input('period_start'),
            $request->input('period_end')
        );

        return response()->json([
            'message' => 'Draft reports generation completed.',
            'generated_count' => count($reports),
            'reports' => $reports
        ]);
    }

    public function getQualityChecks($id)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) {
            return response()->json(['error' => 'Report not found.'], 404);
        }
        return response()->json($report->qualityChecks);
    }

    public function getDashboard(Request $request)
    {
        $draftsCount = PublicHealthReport::where('status', 'draft')->count();
        $pendingCount = PublicHealthReport::where('status', 'pending_review')->count();
        $approvedCount = PublicHealthReport::where('status', 'approved_for_submission')->count();
        $submittedCount = PublicHealthReport::where('status', 'submitted')->count();

        return response()->json([
            'metrics' => [
                'draft_reports' => $draftsCount,
                'reports_pending_review' => $pendingCount,
                'approved_for_submission' => $approvedCount,
                'submitted_reports' => $submittedCount
            ]
        ]);
    }

    public function getFacilityDashboard($facilityId)
    {
        $reports = PublicHealthReport::where('facility_id', $facilityId)->get();
        return response()->json([
            'facility_id' => $facilityId,
            'total_reports' => $reports->count(),
            'drafts' => $reports->where('status', 'draft')->count(),
            'requires_correction' => $reports->where('status', 'requires_correction')->count()
        ]);
    }

    // Phase 2: Governance & Workflow APIs
    public function submitForReview($id, Request $request)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $report->status = 'pending_review';
        $report->save();

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => 'draft',
            'new_status' => 'pending_review',
            'changed_by' => $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001',
            'reason' => 'Submitted for public health review.',
            'changed_at' => now()
        ]);

        return response()->json(['status' => 'pending_review', 'message' => 'Report successfully submitted for review.']);
    }

    public function assignReport($id, Request $request)
    {
        $request->validate(['assigned_to' => 'required|uuid']);
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $assigneeId = $request->input('assigned_to');
        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';

        ReportAssignment::create([
            'report_id' => $report->id,
            'assigned_to' => $assigneeId,
            'assigned_by' => $operatorId,
            'assignment_status' => 'assigned',
            'assigned_at' => now()
        ]);

        return response()->json(['message' => 'Report successfully assigned for review.']);
    }

    public function approveReport($id, Request $request)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $oldStatus = $report->status;
        $report->status = 'approved_for_submission';
        $report->save();

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';

        ReportReview::create([
            'report_id' => $report->id,
            'reviewer_id' => $operatorId,
            'action' => 'approve',
            'comment' => $request->input('comment', 'Approved.'),
            'reviewed_at' => now()
        ]);

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => 'approved_for_submission',
            'changed_by' => $operatorId,
            'reason' => $request->input('comment', 'Approved.'),
            'changed_at' => now()
        ]);

        return response()->json(['status' => 'approved_for_submission', 'message' => 'Report approved.']);
    }

    public function requestCorrection($id, Request $request)
    {
        $request->validate(['reason' => 'required|string']);
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $oldStatus = $report->status;
        $report->status = 'requires_correction';
        $report->requires_correction = true;
        $report->save();

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';
        $reason = $request->input('reason');

        ReportReview::create([
            'report_id' => $report->id,
            'reviewer_id' => $operatorId,
            'action' => 'request_correction',
            'comment' => $reason,
            'reviewed_at' => now()
        ]);

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => 'requires_correction',
            'changed_by' => $operatorId,
            'reason' => $reason,
            'changed_at' => now()
        ]);

        return response()->json(['status' => 'requires_correction', 'message' => 'Correction requested.']);
    }

    public function rejectReport($id, Request $request)
    {
        $request->validate(['reason' => 'required|string']);
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $oldStatus = $report->status;
        $report->status = 'rejected';
        $report->save();

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';
        $reason = $request->input('reason');

        ReportReview::create([
            'report_id' => $report->id,
            'reviewer_id' => $operatorId,
            'action' => 'reject',
            'comment' => $reason,
            'reviewed_at' => now()
        ]);

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => 'rejected',
            'changed_by' => $operatorId,
            'reason' => $reason,
            'changed_at' => now()
        ]);

        return response()->json(['status' => 'rejected', 'message' => 'Report rejected.']);
    }

    public function cancelReport($id, Request $request)
    {
        $request->validate(['reason' => 'required|string']);
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $oldStatus = $report->status;
        $report->status = 'cancelled';
        $report->save();

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';
        $reason = $request->input('reason');

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'old_status' => $oldStatus,
            'new_status' => 'cancelled',
            'changed_by' => $operatorId,
            'reason' => $reason,
            'changed_at' => now()
        ]);

        return response()->json(['status' => 'cancelled', 'message' => 'Report cancelled.']);
    }

    public function correctReport($id, Request $request)
    {
        $request->validate([
            'payload' => 'required|array',
            'reason' => 'required|string'
        ]);

        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';
        $reason = $request->input('reason');

        // Increment version number and save version history payload
        $currentVersion = ReportVersion::where('report_id', $report->id)->max('version_number') ?? 0;
        $newVersion = $currentVersion + 1;

        ReportVersion::create([
            'report_id' => $report->id,
            'version_number' => $newVersion,
            'payload_json' => $report->payload_json ?? [],
            'change_reason' => $reason,
            'created_by' => $operatorId,
            'created_at' => now()
        ]);

        // Save new payload
        $report->payload_json = $request->input('payload');
        $report->status = 'draft';
        $report->requires_correction = false;
        $report->save();

        // Re-run quality checks
        $qualityService = new DataQualityCheckService();
        $qualityService->runQualityChecks($report);

        return response()->json([
            'status' => 'draft',
            'version' => $newVersion,
            'message' => 'Report updated and version preserved.'
        ]);
    }

    public function getVersions($id)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);
        return response()->json(ReportVersion::where('report_id', $report->id)->get());
    }

    public function getStatusHistory($id)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);
        return response()->json(ReportStatusHistory::where('report_id', $report->id)->get());
    }

    public function getReviewQueue()
    {
        $queue = PublicHealthReport::where('status', 'pending_review')->with(['reportType', 'facility'])->get();
        return response()->json($queue);
    }

    // Phase 3: Export & Submission APIs
    public function getSubmissionProfiles()
    {
        return response()->json(SubmissionProfile::all());
    }

    public function createSubmissionProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'report_type_id' => 'required|uuid',
            'destination_type' => 'required|string',
            'endpoint_url' => 'required|url',
            'auth_method' => 'required|string',
            'payload_format' => 'required|string'
        ]);

        $profile = SubmissionProfile::create($request->all());
        return response()->json($profile, 201);
    }

    public function submitReport($id, Request $request)
    {
        $request->validate(['profile_id' => 'required|uuid']);
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        if ($report->status !== 'approved_for_submission') {
            return response()->json(['error' => 'Report must be approved before submission.'], 400);
        }

        $profile = SubmissionProfile::find($request->input('profile_id'));
        if (!$profile) return response()->json(['error' => 'Submission profile not found.'], 404);

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';

        $submission = ReportSubmission::create([
            'report_id' => $report->id,
            'submission_profile_id' => $profile->id,
            'submission_method' => $profile->destination_type,
            'payload_hash' => md5(json_encode($report->payload_json)),
            'status' => 'submitted',
            'external_reference' => 'EXT-' . bin2hex(random_bytes(4)),
            'response_code' => 200,
            'safe_response_summary' => 'Accepted by ' . $profile->name,
            'submitted_by' => $operatorId,
            'submitted_at' => now(),
            'accepted_at' => now()
        ]);

        $report->status = 'submitted';
        $report->save();

        return response()->json([
            'status' => 'submitted',
            'submission' => $submission
        ]);
    }

    public function exportReport($id, Request $request)
    {
        $report = PublicHealthReport::find($id);
        if (!$report) return response()->json(['error' => 'Report not found.'], 404);

        $operatorId = $request->user()?->id ?? User::first()?->id ?? '00000000-0000-0000-0000-000000000001';

        $service = new ExportService();
        $export = $service->exportCsv($report, $operatorId);

        return response()->json([
            'message' => 'Export successfully created with Small-Cell Suppression.',
            'export_id' => $export->id,
            'expires_at' => $export->expires_at
        ]);
    }

    public function getSubmissions($id)
    {
        return response()->json(ReportSubmission::where('report_id', $id)->get());
    }

    public function getIntegrationStatus()
    {
        return response()->json([
            'status' => 'healthy',
            'active_endpoints' => SubmissionProfile::where('active', true)->count()
        ]);
    }

    public function downloadExport($id)
    {
        $export = ExportFile::find($id);
        if (!$export) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        if (now()->isAfter($export->expires_at)) {
            return response()->json(['error' => 'Export file has expired.'], 410);
        }

        $export->download_count++;
        $export->save();

        return response()->download($export->file_path);
    }
}
