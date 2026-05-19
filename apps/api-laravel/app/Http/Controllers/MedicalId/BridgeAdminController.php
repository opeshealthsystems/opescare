<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\BridgeAgent;
use App\Models\BridgeSyncBatch;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BridgeAdminController extends Controller
{
    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    public function index(Request $request)
    {
        $agents = BridgeAgent::where('facility_id', $this->demoFacilityId())
            ->withCount('syncBatches')
            ->orderByDesc('created_at')
            ->paginate(20)->withQueryString();

        $stats = [
            'total'      => BridgeAgent::where('facility_id', $this->demoFacilityId())->count(),
            'active'     => BridgeAgent::where('facility_id', $this->demoFacilityId())->where('status', 'active')->count(),
            'totalBatches' => BridgeSyncBatch::where('facility_id', $this->demoFacilityId())->count(),
            'failedBatches'=> BridgeSyncBatch::where('facility_id', $this->demoFacilityId())->where('status', 'failed')->count(),
        ];

        return view('portals.admin.bridge.index', compact('agents', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $rawKey = 'bak_' . Str::random(40);
        $hash   = hash('sha256', $rawKey);
        $prefix = substr($rawKey, 0, 12);

        BridgeAgent::create([
            'facility_id'    => $this->demoFacilityId(),
            'name'           => $request->name,
            'agent_key'      => $hash,
            'agent_key_prefix' => $prefix,
            'status'         => 'active',
            'notes'          => $request->notes,
            'registered_by'  => session('auth_email') ?: 'demo-admin',
        ]);

        return redirect()->route('portals.admin.bridge')
            ->with('success', 'Bridge Agent registered.')
            ->with('new_agent_key', $rawKey);
    }

    public function toggle(string $id)
    {
        $agent = BridgeAgent::findOrFail($id);
        $agent->update(['status' => $agent->status === 'active' ? 'suspended' : 'active']);

        return redirect()->route('portals.admin.bridge')
            ->with('success', 'Bridge Agent status updated.');
    }

    public function batches(string $id)
    {
        $agent = BridgeAgent::findOrFail($id);
        $batches = BridgeSyncBatch::where('bridge_agent_id', $id)
            ->orderByDesc('created_at')
            ->paginate(30)->withQueryString();

        return view('portals.admin.bridge.batches', compact('agent', 'batches'));
    }
}
