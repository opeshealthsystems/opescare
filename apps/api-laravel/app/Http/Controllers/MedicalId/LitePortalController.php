<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\LiteConflict;
use App\Models\LiteDevice;
use App\Models\LiteOfflineEvent;
use App\Models\Patient;
use App\Modules\OpesCareLite\Services\OpesCareLiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * OpesCare Lite — Simplified portal for small/low-connectivity facilities.
 *
 * Provides a stripped-down, large-button interface covering essential
 * care workflows. The same backend rules apply; only the UI is lighter.
 */
class LitePortalController extends Controller
{
    public function __construct(private readonly OpesCareLiteService $liteService) {}

    // ------------------------------------------------------------------
    // Demo context helpers (inline — no shared trait)
    // ------------------------------------------------------------------

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    // ------------------------------------------------------------------
    // Portal views
    // ------------------------------------------------------------------

    /**
     * Simplified Lite dashboard — today's snapshot.
     */
    public function dashboard(): View
    {
        $facilityId = $this->demoFacilityId();
        $stats      = $this->liteService->getAdminStats($facilityId);

        // Today's queue summary (reuse existing queue table)
        $todayQueue = \App\Models\PatientQueueEntry::where('facility_id', $facilityId)
            ->whereDate('created_at', today())
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status')
            ->all();

        // Recent patients seen today
        $recentPatients = Patient::where('facility_id', $facilityId)
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get(['id', 'first_name', 'last_name', 'health_id', 'updated_at']);

        return view('portals.lite.dashboard', compact(
            'stats', 'todayQueue', 'recentPatients'
        ));
    }

    /**
     * Health ID lookup — search by Health ID, name, or phone.
     */
    public function lookup(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $query      = $request->input('q', '');
        $patients   = collect();

        if (strlen($query) >= 2) {
            $patients = Patient::where('facility_id', $facilityId)
                ->where(function ($q) use ($query) {
                    $q->where('health_id', 'like', "%{$query}%")
                      ->orWhere('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%");
                })
                ->orderBy('last_name')
                ->limit(20)
                ->get(['id', 'first_name', 'last_name', 'health_id', 'date_of_birth', 'phone']);
        }

        return view('portals.lite.lookup', compact('query', 'patients'));
    }

    /**
     * Basic patient registration form.
     */
    public function registerPatientForm(): View
    {
        return view('portals.lite.register_patient');
    }

    /**
     * Store a new patient from Lite registration form.
     */
    public function registerPatientStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|in:male,female,other,unknown',
            'phone'         => 'nullable|string|max:30',
        ]);

        $facilityId = $this->demoFacilityId();

        $patient = Patient::create([
            ...$data,
            'facility_id' => $facilityId,
            'health_id'   => 'HC-' . strtoupper(substr(md5(uniqid()), 0, 8)),
            'created_by'  => $this->demoActorId(),
        ]);

        return redirect()->route('portals.lite.lookup', ['q' => $patient->health_id])
            ->with('success', "Patient {$patient->first_name} {$patient->last_name} registered. Health ID: {$patient->health_id}");
    }

    /**
     * Quick check-in form — add patient to today's queue.
     */
    public function checkIn(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $patientId  = $request->query('patient_id');
        $patient    = $patientId ? Patient::find($patientId) : null;

        return view('portals.lite.checkin', compact('patient'));
    }

    /**
     * Process check-in submission.
     */
    public function checkInStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'patient_id' => 'required|uuid|exists:patients,id',
            'reason'     => 'nullable|string|max:300',
            'priority'   => 'nullable|integer|min:1|max:5',
        ]);

        $facilityId = $this->demoFacilityId();

        \App\Models\PatientQueueEntry::create([
            'patient_id'  => $data['patient_id'],
            'facility_id' => $facilityId,
            'status'      => 'waiting',
            'priority'    => $data['priority'] ?? 3,
            'reason'      => $data['reason'] ?? null,
            'queued_by'   => $this->demoActorId(),
            'queued_at'   => now(),
        ]);

        return redirect()->route('portals.lite.dashboard')
            ->with('success', 'Patient checked in to queue.');
    }

    /**
     * Simple consultation note form.
     */
    public function consultation(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $patient   = $patientId ? Patient::find($patientId) : null;

        return view('portals.lite.consultation', compact('patient'));
    }

    /**
     * Simple billing receipt form.
     */
    public function billing(Request $request): View
    {
        $patientId = $request->query('patient_id');
        $patient   = $patientId ? Patient::find($patientId) : null;

        return view('portals.lite.billing', compact('patient'));
    }

    // ------------------------------------------------------------------
    // Device management (admin-level views)
    // ------------------------------------------------------------------

    /**
     * List all Lite devices for the facility.
     */
    public function devices(): View
    {
        $facilityId = $this->demoFacilityId();
        $devices    = LiteDevice::where('facility_id', $facilityId)
            ->with(['config', 'entitlements'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $stats = $this->liteService->getAdminStats($facilityId);

        return view('portals.lite.devices', compact('devices', 'stats'));
    }

    /**
     * Activate a pending device.
     */
    public function activateDevice(LiteDevice $device): RedirectResponse
    {
        if ($device->facility_id !== $this->demoFacilityId()) {
            abort(403);
        }

        $this->liteService->activateDevice($device);

        return back()->with('success', "Device '{$device->device_name}' activated.");
    }

    /**
     * Revoke a device.
     */
    public function revokeDevice(Request $request, LiteDevice $device): RedirectResponse
    {
        if ($device->facility_id !== $this->demoFacilityId()) {
            abort(403);
        }

        $reason = $request->input('reason', 'Revoked by administrator.');
        $this->liteService->revokeDevice($device, $reason);

        return back()->with('success', "Device '{$device->device_name}' revoked.");
    }

    /**
     * List open sync conflicts for the facility.
     */
    public function conflicts(): View
    {
        $facilityId = $this->demoFacilityId();
        $deviceIds  = LiteDevice::where('facility_id', $facilityId)->pluck('id');

        $conflicts = LiteConflict::whereIn('lite_device_id', $deviceIds)
            ->with(['device', 'offlineEvent'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('portals.lite.conflicts', compact('conflicts'));
    }

    /**
     * Resolve or dismiss a conflict.
     */
    public function resolveConflict(Request $request, LiteConflict $conflict): RedirectResponse
    {
        $data = $request->validate([
            'resolution' => 'required|in:resolved,dismiss',
            'note'       => 'nullable|string|max:500',
        ]);

        $this->liteService->resolveConflict(
            $conflict,
            $this->demoActorId(),
            $data['resolution'],
            $data['note'] ?? ''
        );

        return back()->with('success', 'Conflict ' . $data['resolution'] . '.');
    }

    /**
     * Offline events log for a device.
     */
    public function offlineEvents(LiteDevice $device): View
    {
        if ($device->facility_id !== $this->demoFacilityId()) {
            abort(403);
        }

        $events = LiteOfflineEvent::where('lite_device_id', $device->id)
            ->orderByDesc('captured_at')
            ->paginate(30);

        return view('portals.lite.offline_events', compact('device', 'events'));
    }
}
