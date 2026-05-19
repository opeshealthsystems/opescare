<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\ClinicalAlert;
use App\Models\ClinicalReminder;
use App\Models\ClinicalRule;
use App\Models\DrugInteractionRule;
use App\Models\Facility;
use App\Models\LabAlertRule;
use App\Models\Patient;
use App\Modules\ClinicalDecisionSupport\Services\ClinicalDecisionSupportService;
use Illuminate\Http\Request;

class CdssController extends Controller
{
    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    // -------------------------------------------------------------------------
    // Portal pages
    // -------------------------------------------------------------------------

    /** CDSS overview / active critical alerts dashboard */
    public function index(ClinicalDecisionSupportService $cdss)
    {
        $facilityId = $this->demoFacilityId();

        $criticalCount  = ClinicalAlert::where('facility_id', $facilityId)->where('severity', 'critical')->where('status', 'active')->count();
        $warningCount   = ClinicalAlert::where('facility_id', $facilityId)->where('severity', 'warning')->where('status', 'active')->count();
        $todayTotal     = ClinicalAlert::where('facility_id', $facilityId)->whereDate('triggered_at', today())->count();
        $overrideCount  = ClinicalAlert::where('facility_id', $facilityId)->where('status', 'overridden')->whereDate('updated_at', today())->count();

        $recentAlerts = ClinicalAlert::where('facility_id', $facilityId)
            ->with('patient')
            ->whereIn('status', ['active', 'acknowledged'])
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->orderBy('triggered_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('portals.staff.cdss.index', compact(
            'criticalCount', 'warningCount', 'todayTotal', 'overrideCount', 'recentAlerts'
        ));
    }

    /** Alert history for a specific patient */
    public function patientAlerts(string $patientId, ClinicalDecisionSupportService $cdss)
    {
        $patient = Patient::findOrFail($patientId);
        $alerts  = $cdss->getAlertsForPatient($patientId, 100);

        return view('portals.staff.cdss.patient_alerts', compact('patient', 'alerts'));
    }

    /** Rules management page */
    public function rules()
    {
        $rules = ClinicalRule::orderBy('rule_type')->orderBy('severity')->paginate(25)->withQueryString();
        return view('portals.staff.cdss.rules', compact('rules'));
    }

    /** Lab alert rules management */
    public function labRules()
    {
        $labRules = LabAlertRule::orderBy('lab_test_name')->paginate(25)->withQueryString();
        return view('portals.staff.cdss.lab_rules', compact('labRules'));
    }

    /** Drug interaction rules management */
    public function drugInteractions()
    {
        $interactions = DrugInteractionRule::orderBy('severity')->orderBy('drug_a_name')->paginate(25)->withQueryString();
        return view('portals.staff.cdss.drug_interactions', compact('interactions'));
    }

    // -------------------------------------------------------------------------
    // Actions (AJAX + form POST)
    // -------------------------------------------------------------------------

    /** Acknowledge an alert */
    public function acknowledge(Request $request, string $alertId, ClinicalDecisionSupportService $cdss)
    {
        $request->validate(['alert_id' => 'sometimes|uuid']);

        $alert = $cdss->acknowledgeAlert($alertId, $this->demoActorId());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'acknowledged', 'alert_id' => $alert->id]);
        }
        return back()->with('success', 'Alert acknowledged.');
    }

    /** Override an alert with a required reason */
    public function override(Request $request, string $alertId, ClinicalDecisionSupportService $cdss)
    {
        $request->validate([
            'override_reason'   => 'required|string|min:10|max:500',
            'override_category' => 'required|in:patient_preference,clinical_necessity,allergy_not_confirmed,risk_benefit,other',
        ]);

        $override = $cdss->overrideAlert(
            $alertId,
            $this->demoActorId(),
            $request->override_reason,
            $request->override_category
        );

        if ($request->expectsJson()) {
            return response()->json(['status' => 'overridden', 'override_id' => $override->id]);
        }
        return back()->with('success', 'Alert overridden and reason recorded.');
    }

    /** Dismiss an info-level alert */
    public function dismiss(Request $request, string $alertId, ClinicalDecisionSupportService $cdss)
    {
        $cdss->dismissAlert($alertId, $this->demoActorId());

        if ($request->expectsJson()) {
            return response()->json(['status' => 'dismissed']);
        }
        return back()->with('success', 'Alert dismissed.');
    }

    /** Run CDSS checks (called from consultation/prescription workflow) */
    public function runChecks(Request $request, ClinicalDecisionSupportService $cdss)
    {
        $request->validate([
            'patient_id'    => 'required|uuid',
            'visit_id'      => 'required|uuid',
            'drug_codes'    => 'nullable|array',
            'drug_codes.*'  => 'string',
            'lab_results'   => 'nullable|array',
            'lab_results.*.test_code' => 'required_with:lab_results|string',
            'lab_results.*.value'     => 'required_with:lab_results|numeric',
            'allergies'     => 'nullable|array',
            'allergies.*'   => 'string',
            'is_pregnant'   => 'nullable|boolean',
        ]);

        $firedIds = $cdss->runChecksForVisit(
            $this->demoFacilityId(),
            $request->patient_id,
            $request->visit_id,
            $request->only(['drug_codes', 'lab_results', 'allergies', 'is_pregnant']),
            $this->demoActorId()
        );

        $alerts = ClinicalAlert::whereIn('id', $firedIds)
            ->orderByRaw("CASE severity WHEN 'critical' THEN 1 WHEN 'warning' THEN 2 ELSE 3 END")
            ->get(['id', 'alert_type', 'severity', 'alert_message', 'recommendation', 'status', 'context_data']);

        return response()->json([
            'alerts_fired' => count($firedIds),
            'alerts'       => $alerts,
        ]);
    }

    /** Get active alerts for a visit (AJAX, used in consultation form) */
    public function visitAlerts(Request $request, string $visitId, ClinicalDecisionSupportService $cdss)
    {
        $alerts = $cdss->getActiveAlertsForVisit($visitId);

        if ($request->expectsJson()) {
            return response()->json(['alerts' => $alerts]);
        }

        return view('portals.staff.cdss._alert_panel', compact('alerts', 'visitId'));
    }
}
