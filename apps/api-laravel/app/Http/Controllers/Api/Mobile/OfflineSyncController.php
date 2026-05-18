<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\LocalCachePolicy;
use App\Modules\Offline\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class OfflineSyncController extends Controller
{
    public function createPolicy(Request $request, SyncService $service): JsonResponse
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'uuid'],
            'facility_id' => ['nullable', 'uuid'],
            'device_id' => ['required', 'string', 'max:120'],
            'allowed_scopes' => ['required', 'array', 'min:1'],
            'allowed_scopes.*' => ['string'],
            'emergency_access' => ['nullable', 'boolean'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        try {
            $policy = $service->createLocalCachePolicy($validated, $validated['actor_id'] ?? null);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['data' => $this->serializePolicy($policy)], 201);
    }

    public function queue(LocalCachePolicy $policy, Request $request, SyncService $service): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'array'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        try {
            $queue = $service->queueEncryptedPayload($policy, $validated['payload'], $validated['actor_id'] ?? null);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'data' => [
                'id' => $queue->id,
                'local_cache_policy_id' => $queue->local_cache_policy_id,
                'status' => $queue->status,
                'scopes' => $queue->scopes,
                'payload_hash' => $queue->payload_hash,
                'retry_count' => $queue->retry_count,
            ],
        ], 201);
    }

    private function serializePolicy(LocalCachePolicy $policy): array
    {
        return [
            'id' => $policy->id,
            'patient_id' => $policy->patient_id,
            'facility_id' => $policy->facility_id,
            'device_id' => $policy->device_id,
            'allowed_scopes' => $policy->allowed_scopes,
            'encryption_required' => $policy->encryption_required,
            'encryption_policy' => $policy->encryption_policy,
            'emergency_access' => $policy->emergency_access,
            'review_required' => $policy->review_required,
            'status' => $policy->status,
            'expires_at' => optional($policy->expires_at)->toISOString(),
        ];
    }
}
