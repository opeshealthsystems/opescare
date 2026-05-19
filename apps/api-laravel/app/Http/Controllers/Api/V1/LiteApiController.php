<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LiteConflict;
use App\Models\LiteDevice;
use App\Modules\OpesCareLite\Services\OpesCareLiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * OpesCare Lite Sync & Device API
 *
 * Provides endpoints for Lite client device registration, config fetch,
 * offline event push/pull, and conflict management.
 */
class LiteApiController extends Controller
{
    public function __construct(private readonly OpesCareLiteService $liteService) {}

    /**
     * POST /api/v1/lite/register-device
     *
     * Register a new Lite device for a facility.
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'facility_id'         => 'required|uuid|exists:facilities,id',
            'device_name'         => 'required|string|max:120',
            'device_fingerprint'  => 'required|string|max:128',
            'platform'            => 'nullable|in:web,pwa,tablet,flutter',
            'os_info'             => 'nullable|string|max:200',
            'app_version'         => 'nullable|string|max:40',
            'extra_modules'       => 'nullable|array',
            'extra_modules.*'     => 'string|max:80',
            'offline_allowed'     => 'nullable|boolean',
        ]);

        // Duplicate fingerprint guard
        if (LiteDevice::where('device_fingerprint', $data['device_fingerprint'])->exists()) {
            $device = LiteDevice::where('device_fingerprint', $data['device_fingerprint'])->first();
            return response()->json([
                'message' => 'Device already registered.',
                'device'  => [
                    'id'     => $device->id,
                    'status' => $device->status,
                ],
            ], 200);
        }

        $actorId = $request->input('authorized_by', 'api');

        $device = $this->liteService->registerDevice(
            facilityId:       $data['facility_id'],
            deviceName:       $data['device_name'],
            deviceFingerprint: $data['device_fingerprint'],
            authorizedBy:     $actorId,
            platform:         $data['platform'] ?? 'web',
            extraModules:     $data['extra_modules'] ?? [],
            offlineAllowed:   (bool) ($data['offline_allowed'] ?? false),
        );

        return response()->json([
            'message' => 'Device registered. Awaiting activation.',
            'device'  => [
                'id'          => $device->id,
                'status'      => $device->status,
                'config'      => $this->liteService->getConfig($device),
            ],
        ], 201);
    }

    /**
     * GET /api/v1/lite/config
     *
     * Fetch current config for the authenticated/identified device.
     */
    public function config(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json(['message' => 'Device not found or not active.'], 403);
        }

        $device->touchSeen();

        return response()->json([
            'config' => $this->liteService->getConfig($device),
        ]);
    }

    /**
     * POST /api/v1/lite/sync/push
     *
     * Push offline-captured events to the server for processing.
     */
    public function syncPush(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json(['message' => 'Device not found or not active.'], 403);
        }

        $data = $request->validate([
            'events'                     => 'required|array|min:1|max:200',
            'events.*.event_type'        => 'required|string|max:80',
            'events.*.client_id'         => 'required|string|max:64',
            'events.*.payload'           => 'required|array',
            'events.*.captured_at'       => 'nullable|date',
        ]);

        $result = $this->liteService->pushOfflineEvents($device, $data['events']);

        return response()->json([
            'message' => 'Sync push completed.',
            'result'  => $result,
        ]);
    }

    /**
     * GET /api/v1/lite/sync/pull
     *
     * Pull pending server updates and open conflicts for the device.
     */
    public function syncPull(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json(['message' => 'Device not found or not active.'], 403);
        }

        $since = $request->query('since'); // ISO8601 timestamp

        return response()->json(
            $this->liteService->pullSync($device, $since)
        );
    }

    /**
     * POST /api/v1/lite/offline-events
     *
     * Submit a single offline event (alternative to batch sync push).
     */
    public function offlineEvent(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json(['message' => 'Device not found or not active.'], 403);
        }

        $data = $request->validate([
            'event_type'  => 'required|string|max:80',
            'client_id'   => 'required|string|max:64',
            'payload'     => 'required|array',
            'captured_at' => 'nullable|date',
        ]);

        $result = $this->liteService->pushOfflineEvents($device, [$data]);

        return response()->json([
            'message' => 'Offline event submitted.',
            'result'  => $result,
        ], 201);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Resolve the device from X-Lite-Device-Id header or query param.
     * In a production auth system this would validate a signed token.
     */
    private function resolveDevice(Request $request): ?LiteDevice
    {
        $deviceId = $request->header('X-Lite-Device-Id')
            ?? $request->query('device_id')
            ?? $request->input('device_id');

        if (!$deviceId) {
            return null;
        }

        $device = LiteDevice::find($deviceId);

        if (!$device || !$device->isActive()) {
            return null;
        }

        return $device;
    }
}
