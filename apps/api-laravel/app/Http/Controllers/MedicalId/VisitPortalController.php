<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\ClinicalNote;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\TriageRecord;
use App\Models\Visit;
use App\Models\VitalSign;
use App\Modules\EncounterManagement\Services\ConsultationService;
use App\Modules\OperationalFlow\Services\VisitManagementService;
use App\Modules\Triage\Services\TriageService;
use Illuminate\Http\Request;
use Throwable;

class VisitPortalController extends Controller
{
    // -----------------------------------------------------------------
    // Demo helpers
    // -----------------------------------------------------------------

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    private function demoFacilityId(): ?string
    {
        return Facility::value('id');
    }

    // -----------------------------------------------------------------
    // Visits list
    // -----------------------------------------------------------------

    public function index(Request $req)
    {
        $q = Visit::with(['patient'])
            ->orderByDesc('started_at');

        if ($status = $req->input('status')) {
            $q->where('status', $status);
        }

        if ($patientId = $req->input('patient_id')) {
            $q->where('patient_id', $patientId);
        }

        // Default: exclude completed/cancelled from list unless filtered
        if (!$req->input('status')) {
            $q->whereNotIn('status', ['completed', 'cancelled', 'abandoned']);
        }

        $visits = $q->limit(100)->get();
        $patients = Patient::limit(200)->get();

        return view('portals.staff.visits.index', compact('visits', 'patients'));
    }

    // -----------------------------------------------------------------
    // Create visit
    // -----------------------------------------------------------------

    public function store(Request $req, VisitManagementService $svc)
    {
        $data = $req->validate([
            'patient_id' => 'required|string',
            'visit_type' => 'required|in:general,followup,specialist,emergency,lab,pharmacy',
        ]);

        try {
            $visit = $svc->createVisit(array_merge($data, [
                'facility_id' => $this->demoFacilityId(),
                'provider_id' => null,
            ]));

            return redirect()->route('portals.staff.visits')
                ->with('success', 'Visit #' . substr($visit->id, 0, 8) . ' created.');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to create visit: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------
    // Status transitions
    // -----------------------------------------------------------------

    public function transition(string $id, Request $req, VisitManagementService $svc)
    {
        $data = $req->validate([
            'status' => 'required|string',
        ]);

        try {
            $svc->transition($id, $data['status'], $this->demoActorId());

            return back()->with('success', 'Visit status updated to: ' . ucwords(str_replace('_', ' ', $data['status'])));
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to advance visit: ' . $e->getMessage());
        }
    }

    public function complete(string $id, VisitManagementService $svc)
    {
        try {
            $svc->complete($id, $this->demoActorId());

            return back()->with('success', 'Visit completed successfully.');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to complete visit: ' . $e->getMessage());
        }
    }

    public function cancel(string $id, VisitManagementService $svc)
    {
        try {
            $svc->cancel($id, $this->demoActorId());

            return back()->with('success', 'Visit cancelled.');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to cancel visit: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------
    // Triage
    // -----------------------------------------------------------------

    public function triage(string $id)
    {
        $visit = Visit::with(['patient', 'triageRecords.vitalSigns'])->findOrFail($id);

        return view('portals.staff.visits.triage', compact('visit'));
    }

    public function triageStore(string $id, Request $req, TriageService $svc)
    {
        $data = $req->validate([
            'presenting_complaint' => 'required|string|max:1000',
            'pain_score'           => 'nullable|integer|min:0|max:10',
            'acuity_score'         => 'required|in:critical,urgent,semi_urgent,non_urgent,resuscitation',
            'pregnancy_status'     => 'nullable|string|max:50',
            // vitals
            'temperature'              => 'nullable|numeric|min:20|max:45',
            'blood_pressure_systolic'  => 'nullable|integer|min:40|max:300',
            'blood_pressure_diastolic' => 'nullable|integer|min:20|max:200',
            'pulse'                    => 'nullable|integer|min:20|max:300',
            'respiratory_rate'         => 'nullable|integer|min:4|max:60',
            'oxygen_saturation'        => 'nullable|numeric|min:50|max:100',
            'weight'                   => 'nullable|numeric|min:0.5|max:500',
            'height'                   => 'nullable|numeric|min:20|max:250',
        ]);

        $visit = Visit::findOrFail($id);

        try {
            $vitals = array_filter([
                'temperature'              => $data['temperature'] ?? null,
                'blood_pressure_systolic'  => $data['blood_pressure_systolic'] ?? null,
                'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
                'pulse'                    => $data['pulse'] ?? null,
                'respiratory_rate'         => $data['respiratory_rate'] ?? null,
                'oxygen_saturation'        => $data['oxygen_saturation'] ?? null,
                'weight'                   => $data['weight'] ?? null,
                'height'                   => $data['height'] ?? null,
            ], fn($v) => $v !== null);

            $svc->recordTriage([
                'visit_id'             => $visit->id,
                'patient_id'           => $visit->patient_id,
                'facility_id'          => $visit->facility_id,
                'nurse_id'             => $this->demoActorId(),
                'presenting_complaint' => $data['presenting_complaint'],
                'pain_score'           => $data['pain_score'] ?? null,
                'acuity_score'         => $data['acuity_score'],
                'pregnancy_status'     => $data['pregnancy_status'] ?? null,
                'vitals'               => $vitals ?: null,
            ], $this->demoActorId());

            // Advance visit status
            if ($visit->status === 'open') {
                $visit->update(['status' => 'in_triage']);
            }

            return redirect()->route('portals.staff.visits')
                ->with('success', 'Triage recorded for visit.');
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to record triage: ' . $e->getMessage());
        }
    }

    // -----------------------------------------------------------------
    // Consultation
    // -----------------------------------------------------------------

    public function consult(string $id)
    {
        $visit = Visit::with(['patient', 'clinicalNotes', 'triageRecords.vitalSigns'])->findOrFail($id);

        return view('portals.staff.visits.consult', compact('visit'));
    }

    public function consultStore(string $id, Request $req, ConsultationService $svc, VisitManagementService $visitSvc)
    {
        $data = $req->validate([
            'history_of_present_illness' => 'required|string|min:10|max:5000',
            'examination_findings'       => 'nullable|string|max:5000',
            'treatment_plan'             => 'nullable|string|max:5000',
            'status'                     => 'required|in:draft,signed',
        ]);

        $visit = Visit::findOrFail($id);

        try {
            $svc->saveClinicalNote(array_merge($data, [
                'visit_id'    => $visit->id,
                'provider_id' => $this->demoActorId(),
            ]), $this->demoActorId());

            // Advance visit status to in_consultation if still earlier
            if (in_array($visit->status, ['open', 'in_triage', 'in_queue'])) {
                $visit->update(['status' => 'in_consultation']);
            }

            $msg = $data['status'] === 'signed' ? 'Clinical note signed.' : 'Clinical note saved as draft.';

            return redirect()->route('portals.staff.visits')
                ->with('success', $msg);
        } catch (Throwable $e) {
            return back()->with('error', 'Failed to save clinical note: ' . $e->getMessage());
        }
    }
}
