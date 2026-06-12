<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\Patient;
use Illuminate\Http\Request;

class LabPortalController extends Controller
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
            'pending'    => LabOrder::where('facility_id', $facilityId)
                                ->where('status', 'pending')
                                ->count(),
            'collected'  => LabOrder::where('facility_id', $facilityId)
                                ->where('status', 'collected')
                                ->count(),
            'processing' => LabOrder::where('facility_id', $facilityId)
                                ->where('status', 'processing')
                                ->count(),
            'resulted'   => LabOrder::where('facility_id', $facilityId)
                                ->where('status', 'resulted')
                                ->whereDate('resulted_at', today())
                                ->count(),
            'urgent'     => LabOrder::where('facility_id', $facilityId)
                                ->where('urgency', 'urgent')
                                ->whereNotIn('status', ['resulted', 'cancelled'])
                                ->count(),
            'abnormal'   => LabResult::where('facility_id', $facilityId)
                                ->whereIn('flag', ['H', 'HH', 'L', 'LL', 'abnormal'])
                                ->whereDate('created_at', today())
                                ->count(),
        ];

        $urgentOrders = LabOrder::with('patient')
            ->where('facility_id', $facilityId)
            ->where('urgency', 'urgent')
            ->whereNotIn('status', ['resulted', 'cancelled'])
            ->orderBy('ordered_at')
            ->limit(6)
            ->get();

        $recentResults = LabResult::with(['patient', 'labOrder'])
            ->where('facility_id', $facilityId)
            ->orderByDesc('resulted_at')
            ->limit(6)
            ->get();

        return view('portals.lab.dashboard', compact('stats', 'urgentOrders', 'recentResults'));
    }

    // ------------------------------------------------------------------
    // Work Queue — pending / in-progress orders
    // ------------------------------------------------------------------

    public function orders(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = LabOrder::with('patient')
            ->where('facility_id', $facilityId);

        if ($status = $req->input('status')) {
            $q->where('status', $status);
        } else {
            $q->whereNotIn('status', ['resulted', 'cancelled']);
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

        $orders = $q->orderByRaw("CASE urgency WHEN 'urgent' THEN 0 ELSE 1 END")
                    ->orderBy('ordered_at')
                    ->paginate(25)
                    ->withQueryString();

        return view('portals.lab.orders', compact('orders'));
    }

    // ------------------------------------------------------------------
    // Results — view and enter results
    // ------------------------------------------------------------------

    public function results(Request $req)
    {
        $facilityId = $this->facilityId();

        $q = LabResult::with(['patient', 'labOrder'])
            ->where('facility_id', $facilityId);

        if ($flag = $req->input('flag')) {
            $q->where('flag', $flag);
        }

        if ($search = $req->input('search')) {
            $q->where(function ($sq) use ($search) {
                $sq->where('parameter_name', 'like', "%{$search}%")
                   ->orWhereHas('patient', fn($p) => $p->where('full_name', 'like', "%{$search}%"));
            });
        }

        $results = $q->orderByDesc('resulted_at')->paginate(30)->withQueryString();

        return view('portals.lab.results', compact('results'));
    }

    // ------------------------------------------------------------------
    // Sample Tracking — orders in collection / received state
    // ------------------------------------------------------------------

    public function samples(Request $req)
    {
        $facilityId = $this->facilityId();

        $pending = LabOrder::with('patient')
            ->where('facility_id', $facilityId)
            ->where('status', 'pending')
            ->orderBy('ordered_at')
            ->limit(50)
            ->get();

        $collected = LabOrder::with('patient')
            ->where('facility_id', $facilityId)
            ->where('status', 'collected')
            ->orderByDesc('collected_at')
            ->limit(50)
            ->get();

        return view('portals.lab.samples', compact('pending', 'collected'));
    }

    // ------------------------------------------------------------------
    // Mark sample collected
    // ------------------------------------------------------------------

    public function markCollected(Request $req, string $id)
    {
        $facilityId = $this->facilityId();

        $order = LabOrder::where('facility_id', $facilityId)->findOrFail($id);
        $order->status       = 'collected';
        $order->collected_at = now();
        $order->save();

        return back()->with('success', 'Sample marked as collected.');
    }

    // ------------------------------------------------------------------
    // Mark order in processing
    // ------------------------------------------------------------------

    public function markProcessing(Request $req, string $id)
    {
        $facilityId = $this->facilityId();

        $order = LabOrder::where('facility_id', $facilityId)->findOrFail($id);
        $order->status = 'processing';
        $order->save();

        return back()->with('success', 'Order moved to processing.');
    }
}
