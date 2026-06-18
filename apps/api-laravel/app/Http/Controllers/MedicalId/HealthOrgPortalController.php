<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\HealthOrgOutreachEvent;
use App\Models\HealthOrgProgram;
use App\Models\Patient;
use App\Models\PublicHealthReport;
use App\Models\PublicHealthSignal;
use App\Models\SignalReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HealthOrgPortalController extends Controller
{
    private function facilityId(): ?string
    {
        return session('active_facility_id')
            ?? auth()->user()?->primary_facility_id
            ?? Facility::value('id');
    }

    private function actorId(): ?string
    {
        return (string) (auth()->id() ?? '');
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    public function dashboard()
    {
        $stats = [
            'patients'      => Patient::count(),
            'facilities'    => Facility::where('status', 'active')->count(),
            'programs'      => HealthOrgProgram::where('status', 'active')->count(),
            'outreach'      => HealthOrgOutreachEvent::whereIn('status', ['planned', 'in_progress'])->count(),
            'reports_draft' => PublicHealthReport::where('status', 'draft')->count(),
            'reports_sent'  => PublicHealthReport::where('status', 'submitted')->count(),
            'signals_open'  => PublicHealthSignal::whereNull('resolved_at')->count(),
        ];

        return view('portals.healthorg.dashboard', compact('stats'));
    }

    // ------------------------------------------------------------------
    // Programs
    // ------------------------------------------------------------------

    public function programs()
    {
        $programs = HealthOrgProgram::withCount('outreachEvents')
            ->orderByDesc('created_at')
            ->paginate(20);

        $programTypes = ['immunization', 'maternal', 'nutrition', 'disease_control', 'education', 'screening'];

        return view('portals.healthorg.programs', compact('programs', 'programTypes'));
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'              => 'required|string|max:160',
            'program_type'      => 'nullable|string|max:40',
            'description'       => 'nullable|string|max:1000',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'target_population' => 'nullable|string|max:160',
        ]);

        HealthOrgProgram::create([
            ...$data,
            'facility_id' => $this->facilityId(),
            'status'      => 'active',
            'created_by'  => $this->actorId(),
        ]);

        return redirect()->route('portals.healthorg.programs')->with('success', __('healthorg.program_created'));
    }

    // ------------------------------------------------------------------
    // Outreach events
    // ------------------------------------------------------------------

    public function outreach()
    {
        $events = HealthOrgOutreachEvent::with('program:id,name')
            ->orderByDesc('scheduled_at')
            ->orderByDesc('created_at')
            ->paginate(20);

        $programs = HealthOrgProgram::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('portals.healthorg.outreach', compact('events', 'programs'));
    }

    public function storeOutreach(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'             => 'required|string|max:160',
            'program_id'        => 'nullable|uuid|exists:health_org_programs,id',
            'location'          => 'nullable|string|max:200',
            'scheduled_at'      => 'nullable|date',
            'target_population' => 'nullable|string|max:160',
            'notes'             => 'nullable|string|max:1000',
        ]);

        HealthOrgOutreachEvent::create([
            ...$data,
            'facility_id' => $this->facilityId(),
            'status'      => 'planned',
            'created_by'  => $this->actorId(),
        ]);

        return redirect()->route('portals.healthorg.outreach')->with('success', __('healthorg.outreach_created'));
    }

    public function completeOutreach(Request $request, string $id): RedirectResponse
    {
        $event = HealthOrgOutreachEvent::findOrFail($id);
        $data = $request->validate(['people_reached' => 'nullable|integer|min:0|max:1000000']);

        $event->update([
            'status'         => 'completed',
            'people_reached' => $data['people_reached'] ?? $event->people_reached,
        ]);

        return redirect()->route('portals.healthorg.outreach')->with('success', __('healthorg.outreach_completed'));
    }

    // ------------------------------------------------------------------
    // Public Health Reports
    // ------------------------------------------------------------------

    public function reports()
    {
        $reports = PublicHealthReport::with('reportType:id,name')
            ->orderByDesc('created_at')
            ->paginate(25);

        $reportTypes = DB::table('public_health_report_types')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sensitivity_level', 'default_review_required']);

        return view('portals.healthorg.reports', compact('reports', 'reportTypes'));
    }

    public function storeReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'report_type_id'         => 'required|uuid|exists:public_health_report_types,id',
            'reporting_period_start' => 'required|date',
            'reporting_period_end'   => 'required|date|after_or_equal:reporting_period_start',
            'notes'                  => 'nullable|string|max:2000',
        ]);

        $type = DB::table('public_health_report_types')->find($data['report_type_id']);

        PublicHealthReport::create([
            'report_type_id'         => $data['report_type_id'],
            'facility_id'            => $this->facilityId(),
            'reporting_period_start' => $data['reporting_period_start'],
            'reporting_period_end'   => $data['reporting_period_end'],
            'status'                 => 'draft',
            'sensitivity_level'      => $type->sensitivity_level ?? 'routine',
            'data_classification'    => 'restricted',
            'generated_by_system'    => false,
            'requires_review'        => (bool) ($type->default_review_required ?? false),
            'requires_correction'    => false,
            'payload_json'           => ['notes' => $data['notes'] ?? ''],
            'created_by'             => $this->actorId(),
        ]);

        return redirect()->route('portals.healthorg.reports')->with('success', __('healthorg.report_created'));
    }

    public function submitReport(Request $request, string $id): RedirectResponse
    {
        $report = PublicHealthReport::findOrFail($id);
        abort_if($report->status !== 'draft', 422, 'Only draft reports can be submitted.');

        $report->update(['status' => 'submitted', 'updated_by' => $this->actorId()]);

        return redirect()->route('portals.healthorg.reports')->with('success', __('healthorg.report_submitted'));
    }

    // ------------------------------------------------------------------
    // Signals — review / triage
    // ------------------------------------------------------------------

    public function signals()
    {
        $signals = PublicHealthSignal::with('facility:id,name')
            ->orderByDesc('detected_at')
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('portals.healthorg.signals', compact('signals'));
    }

    public function reviewSignal(Request $request, string $id): RedirectResponse
    {
        $signal = PublicHealthSignal::findOrFail($id);

        $data = $request->validate([
            'action'  => 'required|in:confirm,dismiss,escalate,resolve',
            'comment' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($signal, $data) {
            SignalReview::create([
                'signal_id'   => $signal->id,
                'reviewer_id' => $this->actorId(),
                'action'      => $data['action'],
                'comment'     => $data['comment'] ?? null,
                'reviewed_at' => now(),
            ]);

            $update = ['reviewed_at' => now()];
            $update['status'] = match ($data['action']) {
                'confirm'  => 'confirmed',
                'dismiss'  => 'dismissed',
                'escalate' => 'escalated',
                'resolve'  => 'resolved',
            };
            if ($data['action'] === 'resolve') {
                $update['resolved_at'] = now();
            }
            $signal->update($update);
        });

        return redirect()->route('portals.healthorg.signals')->with('success', __('healthorg.signal_reviewed'));
    }
}
