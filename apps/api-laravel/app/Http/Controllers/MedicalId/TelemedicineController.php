<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\CareFacility;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\Teleconsultation;
use App\Models\VirtualWaitingRoom;
use App\Modules\Telemedicine\Services\TelemedicineService;
use App\Modules\Telemedicine\Services\TelemedicineConsentService;
use App\Modules\Telemedicine\Services\VirtualWaitingRoomService;
use Illuminate\Http\Request;
use Throwable;

/**
 * TelemedicineController — Module 18 (Telemedicine)
 *
 * Portal controller for telemedicine consultations.
 * Handles scheduling, consent, waiting room, and call lifecycle.
 *
 * OpesCare disclaimer: The platform facilitates connections.
 * Clinical decisions are the provider's responsibility.
 */
class TelemedicineController extends Controller
{
    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-provider';
    }

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? CareFacility::value('id') ?? 'demo-facility';
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index(VirtualWaitingRoomService $waitingRoomSvc)
    {
        $facilityId = $this->demoFacilityId();

        $scheduled = Teleconsultation::where('facility_id', $facilityId)
            ->whereIn('status', ['scheduled', 'waiting'])
            ->with('patient')
            ->orderBy('scheduled_at')
            ->paginate(20);

        $today = Teleconsultation::where('facility_id', $facilityId)
            ->whereDate('scheduled_at', today())
            ->count();

        $waiting = VirtualWaitingRoom::where('facility_id', $facilityId)
            ->where('status', 'waiting')
            ->count();

        $completed = Teleconsultation::where('facility_id', $facilityId)
            ->where('status', 'completed')
            ->whereDate('updated_at', today())
            ->count();

        return view('portals.staff.telemedicine.index', compact(
            'scheduled', 'today', 'waiting', 'completed'
        ));
    }

    // ── Schedule consultation ─────────────────────────────────────────────────

    public function create()
    {
        $patients = Patient::select('id', 'first_name', 'last_name', 'health_id')
            ->orderBy('last_name')
            ->limit(100)
            ->get();

        return view('portals.staff.telemedicine.create', compact('patients'));
    }

    public function store(Request $request, TelemedicineService $svc)
    {
        $data = $request->validate([
            'patient_id'   => 'required|uuid|exists:patients,id',
            'provider_id'  => 'nullable|string',
            'scheduled_at' => 'required|date|after:now',
            'platform'     => 'nullable|in:own,zoom,meet,teams',
        ]);

        try {
            $consultation = $svc->schedule(array_merge($data, [
                'facility_id' => $this->demoFacilityId(),
                'provider_id' => $data['provider_id'] ?? $this->demoActorId(),
            ]));

            return redirect()
                ->route('portals.staff.telemedicine.show', $consultation->id)
                ->with('success', 'Teleconsultation scheduled successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Show consultation ─────────────────────────────────────────────────────

    public function show(string $id)
    {
        $consultation = Teleconsultation::with(['patient', 'consent', 'waitingRoom', 'callSession', 'notes'])
            ->findOrFail($id);

        return view('portals.staff.telemedicine.show', compact('consultation'));
    }

    // ── Start / join consultation ─────────────────────────────────────────────

    public function startCall(Request $request, string $id, TelemedicineService $svc, TelemedicineConsentService $consentSvc)
    {
        $consultation = Teleconsultation::findOrFail($id);

        if (! $consentSvc->canProceed($consultation)) {
            return back()->with('error', 'Patient consent must be obtained before starting the call.');
        }

        try {
            $session = $svc->startCall($consultation);
            return redirect()
                ->route('portals.staff.telemedicine.show', $consultation->id)
                ->with('success', 'Call started.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── End consultation ──────────────────────────────────────────────────────

    public function endCall(Request $request, string $id, TelemedicineService $svc)
    {
        $consultation = Teleconsultation::with('callSession')->findOrFail($id);

        if (! $consultation->callSession) {
            return back()->with('error', 'No active call session found.');
        }

        try {
            $svc->endCall($consultation, $consultation->callSession);
            return redirect()
                ->route('portals.staff.telemedicine.show', $consultation->id)
                ->with('success', 'Consultation completed.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Cancel consultation ───────────────────────────────────────────────────

    public function cancel(Request $request, string $id, TelemedicineService $svc)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $consultation = Teleconsultation::findOrFail($id);

        try {
            $svc->cancel($consultation, $data['reason']);
            return redirect()
                ->route('portals.staff.telemedicine.index')
                ->with('success', 'Consultation cancelled.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Record consent ────────────────────────────────────────────────────────

    public function recordConsent(Request $request, string $id, TelemedicineConsentService $consentSvc)
    {
        $data = $request->validate([
            'consent_method'       => 'required|in:verbal,digital,written',
            'consent_text_version' => 'nullable|string|max:50',
        ]);

        $consultation = Teleconsultation::findOrFail($id);

        try {
            $consentSvc->grantConsent(
                $consultation,
                $consultation->patient_id,
                $data['consent_method'],
                $data['consent_text_version'] ?? '1.0',
            );

            return back()->with('success', 'Consent recorded.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Waiting room dashboard ────────────────────────────────────────────────

    public function waitingRoom(VirtualWaitingRoomService $svc)
    {
        $facilityId = $this->demoFacilityId();
        $waiting    = $svc->waitingPatients($facilityId);
        $estimated  = $svc->estimateWait($facilityId);

        return view('portals.staff.telemedicine.waiting_room', compact('waiting', 'estimated'));
    }

    // ── Call next patient ─────────────────────────────────────────────────────

    public function callNext(VirtualWaitingRoomService $svc)
    {
        $next = $svc->callNext($this->demoFacilityId());

        if (! $next) {
            return back()->with('info', 'No patients in the waiting room.');
        }

        return redirect()
            ->route('portals.staff.telemedicine.show', $next->teleconsultation_id)
            ->with('success', "Called patient from waiting room.");
    }
}
