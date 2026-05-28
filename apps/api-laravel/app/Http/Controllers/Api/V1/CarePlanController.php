<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Clinical\CarePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarePlanController extends Controller
{
    public function __construct(private readonly CarePlanService $service)
    {
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'patient_id'  => 'required|uuid|exists:patients,id',
            'facility_id' => 'required|uuid|exists:facilities,id',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date|after_or_equal:start_date',
            'status'      => 'nullable|in:active,completed,on_hold,cancelled',
            'visit_id'    => 'nullable|uuid',
        ]);

        $validated['created_by'] = $request->user()->id;

        $plan = $this->service->create($validated);

        return response()->json(['data' => $plan], 201);
    }

    public function show(string $id): JsonResponse
    {
        $summary = $this->service->getSummary($id);
        return response()->json(['data' => $summary]);
    }

    public function storeGoal(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'goal_text'   => 'required|string',
            'target_date' => 'nullable|date',
            'notes'       => 'nullable|string',
        ]);

        $goal = $this->service->addGoal($id, $validated);
        return response()->json(['data' => $goal], 201);
    }

    public function updateGoal(Request $request, string $id, string $goalId): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,achieved,abandoned',
        ]);

        $goal = $this->service->updateGoalStatus($goalId, $validated['status']);
        return response()->json(['data' => $goal]);
    }

    public function storeIntervention(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'intervention_type' => 'required|in:medication,exercise,diet,monitoring,referral,education,other',
            'description'       => 'required|string',
            'frequency'         => 'nullable|string|max:100',
            'responsible_party' => 'nullable|string|max:100',
        ]);

        $intervention = $this->service->addIntervention($id, $validated);
        return response()->json(['data' => $intervention], 201);
    }
}
