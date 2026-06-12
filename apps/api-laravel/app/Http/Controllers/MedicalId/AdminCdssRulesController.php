<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\DrugInteractionRule;
use App\Models\AllergyAlertRule;
use App\Models\LabAlertRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCdssRulesController extends Controller
{
    private function actorId(): string
    {
        return Auth::id() ?? session('auth_email', 'system');
    }

    public function index()
    {
        if (!Auth::check()) return redirect()->route('login');

        $stats = [];

        try {
            $stats['drug_interactions_total'] = DrugInteractionRule::count();
            $stats['drug_interactions_active'] = DrugInteractionRule::where('is_active', true)->count();
        } catch (\Throwable $e) {
            $stats['drug_interactions_total'] = 0;
            $stats['drug_interactions_active'] = 0;
        }

        try {
            $stats['allergy_alerts_total'] = AllergyAlertRule::count();
            $stats['allergy_alerts_active'] = AllergyAlertRule::where('is_active', true)->count();
        } catch (\Throwable $e) {
            $stats['allergy_alerts_total'] = 0;
            $stats['allergy_alerts_active'] = 0;
        }

        try {
            $stats['lab_alerts_total'] = LabAlertRule::count();
            $stats['lab_alerts_active'] = LabAlertRule::where('is_active', true)->count();
        } catch (\Throwable $e) {
            $stats['lab_alerts_total'] = 0;
            $stats['lab_alerts_active'] = 0;
        }

        return view('portals.admin.cdss.index', compact('stats'));
    }

    public function drugInteractions(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            $query = DrugInteractionRule::query();

            if ($request->filled('severity')) {
                $query->where('severity', $request->input('severity'));
            }

            $rules = $query->orderBy('created_at', 'desc')
                ->paginate(25)
                ->withQueryString();
        } catch (\Throwable $e) {
            $rules = collect()->paginate(25);
            return redirect()->back()->with('error', 'Unable to load drug interaction rules: ' . $e->getMessage());
        }

        $severities = ['mild', 'moderate', 'severe', 'contraindicated'];

        return view('portals.admin.cdss.drug_interactions', compact('rules', 'severities'));
    }

    public function storeDrugInteraction(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $validated = $request->validate([
            'drug_a'             => 'required|string|max:255',
            'drug_b'             => 'required|string|max:255',
            'severity'           => 'required|in:mild,moderate,severe,contraindicated',
            'description'        => 'nullable|string',
            'action_required'    => 'nullable|string',
        ]);

        try {
            DrugInteractionRule::create([
                'drug_a_name'              => $validated['drug_a'],
                'drug_a_code'              => $request->input('drug_a_code', ''),
                'drug_b_name'              => $validated['drug_b'],
                'drug_b_code'              => $request->input('drug_b_code', ''),
                'severity'                 => $validated['severity'],
                'interaction_description'  => $validated['description'] ?? null,
                'management'               => $validated['action_required'] ?? null,
                'clinical_effect'          => $request->input('clinical_effect'),
                'is_active'                => true,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to create drug interaction rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Drug interaction rule created successfully.');
    }

    public function destroyDrugInteraction(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            DrugInteractionRule::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to delete drug interaction rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Drug interaction rule deleted.');
    }

    public function allergyAlerts(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            $rules = AllergyAlertRule::query()
                ->orderBy('created_at', 'desc')
                ->paginate(25)
                ->withQueryString();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to load allergy alert rules: ' . $e->getMessage());
        }

        return view('portals.admin.cdss.allergy_alerts', compact('rules'));
    }

    public function storeAllergyAlert(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $validated = $request->validate([
            'drug_name'      => 'required|string|max:255',
            'allergen_class' => 'required|string|max:255',
            'severity'       => 'required|string|max:100',
            'reaction_type'  => 'nullable|string|max:255',
        ]);

        try {
            AllergyAlertRule::create([
                'drug_name'             => $validated['drug_name'],
                'drug_code'             => $request->input('drug_code', ''),
                'allergen_name'         => $validated['allergen_class'],
                'allergen_code'         => $request->input('allergen_code', ''),
                'cross_reactivity_group'=> $validated['allergen_class'],
                'severity'              => $validated['severity'],
                'alert_message'         => $validated['reaction_type'] ?? null,
                'is_active'             => true,
                'is_hard_stop'          => (bool) $request->input('is_hard_stop', false),
                'facility_id'           => $request->input('facility_id'),
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to create allergy alert rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Allergy alert rule created successfully.');
    }

    public function destroyAllergyAlert(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            AllergyAlertRule::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to delete allergy alert rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Allergy alert rule deleted.');
    }

    public function labAlerts(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            $rules = LabAlertRule::query()
                ->orderBy('created_at', 'desc')
                ->paginate(25)
                ->withQueryString();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to load lab alert rules: ' . $e->getMessage());
        }

        return view('portals.admin.cdss.lab_alerts', compact('rules'));
    }

    public function storeLabAlert(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $validated = $request->validate([
            'test_code'       => 'required|string|max:255',
            'condition'       => 'required|in:above,below,equals',
            'threshold_value' => 'required|numeric',
            'severity'        => 'required|string|max:100',
            'message'         => 'required|string',
        ]);

        try {
            $data = [
                'lab_test_code'  => $validated['test_code'],
                'lab_test_name'  => $request->input('test_name', $validated['test_code']),
                'unit'           => $request->input('unit'),
                'is_active'      => true,
                'gender_filter'  => $request->input('gender_filter'),
                'age_min'        => $request->input('age_min'),
                'age_max'        => $request->input('age_max'),
            ];

            $threshold = (float) $validated['threshold_value'];

            switch ($validated['condition']) {
                case 'above':
                    $data['normal_high']   = $threshold;
                    $data['critical_high'] = $request->input('critical_high', $threshold * 1.5);
                    break;
                case 'below':
                    $data['normal_low']   = $threshold;
                    $data['critical_low'] = $request->input('critical_low', $threshold * 0.5);
                    break;
                case 'equals':
                    $data['normal_low']  = $threshold;
                    $data['normal_high'] = $threshold;
                    break;
            }

            LabAlertRule::create($data);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to create lab alert rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Lab alert rule created successfully.');
    }

    public function destroyLabAlert(string $id)
    {
        if (!Auth::check()) return redirect()->route('login');

        try {
            LabAlertRule::findOrFail($id)->delete();
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Failed to delete lab alert rule: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Lab alert rule deleted.');
    }
}
