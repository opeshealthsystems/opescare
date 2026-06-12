<?php

namespace App\Http\Controllers\MedicalId;

use App\Models\Facility;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AdminFacilityManagementController
{
    private function actorId(): string
    {
        return Auth::id() ?? session('auth_email', 'system');
    }

    public function index(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $query = Facility::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('license_number', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $facilities = $query->paginate(25)->withQueryString();

        $stats = [
            'total'            => Facility::count(),
            'active'           => Facility::where('status', 'active')->count(),
            'suspended'        => Facility::where('status', 'suspended')->count(),
            'pending_approval' => Facility::where('status', 'pending_approval')->count(),
        ];

        return view('portals.admin.facilities.index', compact('facilities', 'stats'));
    }

    public function show(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);

        $staffCount   = $facility->users()->count();
        $patientCount = Patient::where('facility_id', $id)->count();

        return view('portals.admin.facilities.show', compact('facility', 'staffCount', 'patientCount'));
    }

    public function store(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $data = $request->validate([
            'name'         => 'required|max:200',
            'type'         => 'required',
            'region'       => 'nullable',
            'country_code' => 'nullable|max:3',
        ]);

        $data['status'] = 'active';

        if (empty($data['facility_code'])) {
            $region = strtoupper($data['region'] ?? 'XX');
            $data['facility_code'] = 'OP-' . $region . '-FID-' . strtoupper(substr(md5(uniqid()), 0, 4));
        }

        Facility::create($data);

        return redirect()->route('admin.facilities.index')->with('success', 'Facility created successfully.');
    }

    public function update(Request $request, string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);

        $data = $request->validate([
            'name'                   => 'sometimes|required|max:200',
            'type'                   => 'sometimes|required',
            'status'                 => 'sometimes|nullable',
            'license_number'         => 'sometimes|nullable',
            'parent_organization_id' => 'sometimes|nullable',
            'region'                 => 'sometimes|nullable',
            'country_code'           => 'sometimes|nullable|max:3',
        ]);

        $facility->update($data);

        return redirect()->back()->with('success', 'Facility updated successfully.');
    }

    public function suspend(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);
        $facility->update(['status' => 'suspended']);

        return redirect()->back()->with('success', 'Facility suspended.');
    }

    public function activate(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);
        $facility->update(['status' => 'active']);

        return redirect()->back()->with('success', 'Facility activated.');
    }

    public function approve(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);

        $updates = ['status' => 'active'];

        if (in_array('approved_at', $facility->getFillable()) || \Illuminate\Support\Facades\Schema::hasColumn($facility->getTable(), 'approved_at')) {
            $updates['approved_at'] = Carbon::now();
        }

        $facility->update($updates);

        return redirect()->back()->with('success', 'Facility approved.');
    }

    public function destroy(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        $facility = Facility::findOrFail($id);

        if (Patient::where('facility_id', $id)->exists()) {
            return redirect()->back()->with('error', 'Cannot delete facility with active records.');
        }

        $facility->delete();

        return redirect()->route('admin.facilities.index')->with('success', 'Facility deleted.');
    }
}
