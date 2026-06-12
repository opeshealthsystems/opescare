<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Prescription;
use Illuminate\Http\Request;

/**
 * Staff-facing clinical listing pages for /portals/staff.
 *
 * Gives doctors, nurses, and specialists a facility-wide view of all
 * prescriptions and lab orders — complementing the per-visit consult workflow.
 */
class StaffClinicalController extends Controller
{
    private function facilityId(): ?string
    {
        return session('active_facility_id')
            ?? auth()->user()?->primary_facility_id
            ?? Facility::value('id');
    }

    // ------------------------------------------------------------------
    // Prescriptions register
    // ------------------------------------------------------------------

    public function prescriptions(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = Prescription::with(['patient', 'items'])
            ->where('facility_id', $facilityId);

        if ($status = $req->input('status')) {
            $q->where('status', $status);
        }

        if ($search = $req->input('search')) {
            $q->whereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$search}%"));
        }

        $prescriptions = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        $summary = [
            'active'              => Prescription::where('facility_id', $facilityId)->where('status', 'active')->count(),
            'dispensed_today'     => Prescription::where('facility_id', $facilityId)->where('status', 'dispensed')->whereDate('dispensed_at', today())->count(),
            'partially_dispensed' => Prescription::where('facility_id', $facilityId)->where('status', 'partially_dispensed')->count(),
            'expired'             => Prescription::where('facility_id', $facilityId)->where('status', 'expired')->count(),
        ];

        return view('portals.staff.clinical.prescriptions', compact('prescriptions', 'summary'));
    }

    // ------------------------------------------------------------------
    // Lab orders register
    // ------------------------------------------------------------------

    public function labOrders(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = LabOrder::with('patient')
            ->where('facility_id', $facilityId);

        if ($status = $req->input('status')) {
            $q->where('status', $status);
        }

        if ($urgency = $req->input('urgency')) {
            $q->where('urgency', $urgency);
        }

        if ($search = $req->input('search')) {
            $q->where(function ($sq) use ($search) {
                $sq->where('test_name', 'like', "%{$search}%")
                   ->orWhereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$search}%"));
            });
        }

        $orders = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        $summary = [
            'pending'    => LabOrder::where('facility_id', $facilityId)->where('status', 'pending')->count(),
            'processing' => LabOrder::where('facility_id', $facilityId)->where('status', 'processing')->count(),
            'resulted'   => LabOrder::where('facility_id', $facilityId)->where('status', 'resulted')->whereDate('resulted_at', today())->count(),
            'urgent'     => LabOrder::where('facility_id', $facilityId)->where('urgency', 'urgent')->whereNotIn('status', ['resulted', 'cancelled'])->count(),
        ];

        return view('portals.staff.clinical.lab_orders', compact('orders', 'summary'));
    }
}
