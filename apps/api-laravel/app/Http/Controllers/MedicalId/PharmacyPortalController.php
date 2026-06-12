<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\PharmacyInventory;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use Illuminate\Http\Request;

class PharmacyPortalController extends Controller
{
    private function facilityId(): ?string
    {
        return session('active_facility_id')
            ?? auth()->user()?->primary_facility_id
            ?? Facility::value('id');
    }

    // ------------------------------------------------------------------
    // Dashboard
    // ------------------------------------------------------------------

    public function dashboard()
    {
        $facilityId = $this->facilityId();

        $stats = [
            'pending_rx'     => Prescription::where('facility_id', $facilityId)
                                    ->whereIn('status', ['active', 'partially_dispensed'])
                                    ->count(),
            'dispensed_today' => Prescription::where('facility_id', $facilityId)
                                    ->where('status', 'dispensed')
                                    ->whereDate('dispensed_at', today())
                                    ->count(),
            'total_drugs'    => PharmacyInventory::where('facility_id', $facilityId)->count(),
            'low_stock'      => PharmacyInventory::where('facility_id', $facilityId)
                                    ->where('stock_status', 'low_stock')
                                    ->count(),
            'expired'        => PharmacyInventory::where('facility_id', $facilityId)
                                    ->where('is_expired', true)
                                    ->count(),
            'out_of_stock'   => PharmacyInventory::where('facility_id', $facilityId)
                                    ->where('stock_status', 'out_of_stock')
                                    ->count(),
        ];

        $pendingRx = Prescription::with(['patient', 'items'])
            ->where('facility_id', $facilityId)
            ->whereIn('status', ['active', 'partially_dispensed'])
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();

        $alerts = PharmacyInventory::where('facility_id', $facilityId)
            ->where(function ($q) {
                $q->where('is_expired', true)
                  ->orWhere('stock_status', 'out_of_stock')
                  ->orWhere('stock_status', 'low_stock');
            })
            ->orderByRaw("CASE stock_status WHEN 'out_of_stock' THEN 0 WHEN 'low_stock' THEN 1 ELSE 2 END")
            ->limit(6)
            ->get();

        return view('portals.pharmacy.dashboard', compact('stats', 'pendingRx', 'alerts'));
    }

    // ------------------------------------------------------------------
    // Prescription Queue
    // ------------------------------------------------------------------

    public function prescriptions(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = Prescription::with(['patient', 'items'])
            ->where('facility_id', $facilityId);

        if ($status = $req->input('status')) {
            $q->where('status', $status);
        } else {
            $q->whereIn('status', ['active', 'partially_dispensed']);
        }

        if ($search = $req->input('search')) {
            $q->whereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$search}%"));
        }

        $prescriptions = $q->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('portals.pharmacy.prescriptions', compact('prescriptions'));
    }

    // ------------------------------------------------------------------
    // Dispense (mark a prescription as dispensed)
    // ------------------------------------------------------------------

    public function dispense(Request $req, string $id)
    {
        $facilityId = $this->facilityId();

        $rx = Prescription::where('facility_id', $facilityId)->findOrFail($id);

        $rx->status       = 'dispensed';
        $rx->dispensed_at = now();
        $rx->save();

        return redirect()->route('portals.pharmacy.prescriptions')
            ->with('success', 'Prescription marked as dispensed.');
    }

    // ------------------------------------------------------------------
    // Drug Inventory
    // ------------------------------------------------------------------

    public function inventory(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = PharmacyInventory::where('facility_id', $facilityId);

        if ($search = $req->input('search')) {
            $q->where(function ($sq) use ($search) {
                $sq->where('medicine_name', 'like', "%{$search}%")
                   ->orWhere('generic_name', 'like', "%{$search}%");
            });
        }

        if ($status = $req->input('stock_status')) {
            $q->where('stock_status', $status);
        }

        $drugs = $q->orderBy('medicine_name')->paginate(30)->withQueryString();

        return view('portals.pharmacy.inventory', compact('drugs'));
    }

    // ------------------------------------------------------------------
    // Controlled Substances Log
    // ------------------------------------------------------------------

    public function controlled()
    {
        $facilityId = $this->facilityId();

        $controlled = PharmacyInventory::where('facility_id', $facilityId)
            ->where('is_recalled', false)
            ->orderBy('medicine_name')
            ->limit(100)
            ->get();

        $recentRx = Prescription::with(['patient', 'items'])
            ->where('facility_id', $facilityId)
            ->whereHas('items')
            ->orderByDesc('dispensed_at')
            ->limit(20)
            ->get();

        return view('portals.pharmacy.controlled', compact('controlled', 'recentRx'));
    }
}
