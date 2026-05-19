<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Bed;
use App\Models\Facility;
use App\Models\Ward;
use App\Modules\WardManagement\Services\WardService;
use Illuminate\Http\Request;
use Throwable;

class WardController extends Controller
{
    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? 'demo-facility';
    }

    // ── Ward overview / bed map ───────────────────────────────────

    public function index(WardService $svc)
    {
        $facilityId = $this->demoFacilityId();
        $summary    = $svc->occupancySummary($facilityId);
        $wards      = Ward::where('facility_id', $facilityId)
            ->where('is_active', true)
            ->with(['beds.activeAdmission.patient'])
            ->orderBy('name')
            ->get();

        return view('portals.staff.wards.index', compact('summary', 'wards'));
    }

    // ── Create ward ───────────────────────────────────────────────

    public function wardStore(Request $request, WardService $svc)
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'ward_type'  => 'required|in:' . implode(',', array_keys(Ward::wardTypes())),
            'total_beds' => 'required|integer|min:1|max:200',
            'floor'      => 'nullable|string|max:20',
            'building'   => 'nullable|string|max:50',
        ]);

        try {
            $svc->createWard(array_merge($request->validated(), [
                'facility_id' => $this->demoFacilityId(),
                'is_active'   => true,
            ]));

            return redirect()->route('portals.staff.wards')->with('success', 'Ward created with beds.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Admissions list ───────────────────────────────────────────

    public function admissions(Request $request)
    {
        $q = Admission::with(['patient', 'bed.ward'])
            ->where('facility_id', $this->demoFacilityId())
            ->orderByDesc('admitted_at');

        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }

        $admissions = $q->paginate(20)->withQueryString();

        return view('portals.staff.wards.admissions', compact('admissions'));
    }

    // ── Admit patient ─────────────────────────────────────────────

    public function admitStore(Request $request, WardService $svc)
    {
        $request->validate([
            'patient_id'      => 'required|string|max:100',
            'bed_id'          => 'required|uuid',
            'admission_reason'=> 'nullable|string|max:500',
            'visit_id'        => 'nullable|string|max:100',
        ]);

        try {
            $svc->admit(array_merge($request->validated(), [
                'facility_id' => $this->demoFacilityId(),
            ]), $this->demoActorId());

            return redirect()->route('portals.staff.wards.admissions')
                ->with('success', 'Patient admitted successfully.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    // ── Discharge ─────────────────────────────────────────────────

    public function dischargeStore(Request $request, string $id, WardService $svc)
    {
        $request->validate([
            'discharge_reason'     => 'nullable|string|max:500',
            'discharge_destination'=> 'required|in:home,referral,ama,deceased,transferred',
        ]);

        try {
            $admission = Admission::findOrFail($id);
            $svc->discharge($admission, $request->validated(), $this->demoActorId());

            return redirect()->route('portals.staff.wards.admissions')
                ->with('success', 'Patient discharged.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Transfer ──────────────────────────────────────────────────

    public function transferStore(Request $request, string $id, WardService $svc)
    {
        $request->validate([
            'to_bed_id' => 'required|uuid',
            'reason'    => 'nullable|string|max:300',
        ]);

        try {
            $admission = Admission::with('bed')->findOrFail($id);
            $svc->transfer($admission, $request->to_bed_id, $request->reason, $this->demoActorId());

            return redirect()->route('portals.staff.wards.admissions')
                ->with('success', 'Bed transfer completed.');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
