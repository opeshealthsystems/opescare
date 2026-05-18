<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Modules\FacilityReadiness\Services\FacilityGoLiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;
use RuntimeException;

class FacilityGoLiveReadinessController extends Controller
{
    public function show(Facility $facility, FacilityGoLiveService $service): JsonResponse
    {
        return response()->json([
            'data' => $this->serialize($service->getOrCreateReadiness($facility->id), $service),
        ]);
    }

    public function store(Facility $facility, Request $request, FacilityGoLiveService $service): JsonResponse
    {
        $readiness = $service->getOrCreateReadiness($facility->id, $request->input('actor_id'));

        return response()->json(['data' => $this->serialize($readiness, $service)], 201);
    }

    public function markItem(Facility $facility, string $item, Request $request, FacilityGoLiveService $service): JsonResponse
    {
        $request->validate([
            'complete' => ['required', 'boolean'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        $readiness = $service->getOrCreateReadiness($facility->id, $request->input('actor_id'));

        try {
            $readiness = $service->markItem($readiness, $item, $request->boolean('complete'), $request->input('actor_id'));
        } catch (InvalidArgumentException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => $this->serialize($readiness, $service)]);
    }

    public function approve(Facility $facility, Request $request, FacilityGoLiveService $service): JsonResponse
    {
        $validated = $request->validate([
            'actor_id' => ['required', 'uuid'],
            'approval_note' => ['required', 'string', 'max:2000'],
        ]);

        $readiness = $service->getOrCreateReadiness($facility->id, $validated['actor_id']);

        try {
            $readiness = $service->approveGoLive($readiness, $validated['actor_id'], $validated['approval_note']);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => $this->serialize($readiness, $service)]);
    }

    private function serialize($readiness, FacilityGoLiveService $service): array
    {
        return [
            'id' => $readiness->id,
            'facility_id' => $readiness->facility_id,
            'status' => $readiness->status,
            'can_go_live' => $readiness->can_go_live,
            'checklist' => $readiness->checklist_json,
            'checklist_labels' => $service->checklistLabels(),
            'missing_items' => $service->missingItems($readiness),
            'risks' => $service->risks($readiness),
            'approved_by' => $readiness->approved_by,
            'approved_at' => optional($readiness->approved_at)->toISOString(),
            'approval_note' => $readiness->approval_note,
        ];
    }
}
