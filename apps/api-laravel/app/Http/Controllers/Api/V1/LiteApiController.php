<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LiteConflict;
use App\Models\LiteDevice;
use App\Modules\OpesCareLite\Services\OpesCareLiteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * OpesCare Lite Sync & Device API
 *
 * Provides endpoints for Lite client device registration, config fetch,
 * offline event push/pull, and conflict management.
 *
 * ## Request Authentication (HMAC-SHA256)
 *
 * After registration, every request to authenticated Lite endpoints must include:
 *
 *   X-Lite-Device-Id:  <device_uuid>
 *   X-Lite-Timestamp:  <unix_timestamp>   (UTC, 5-minute replay window)
 *   X-Lite-Signature:  HMAC-SHA256(<device_id>.<timestamp>.<sha256(body)>, device_secret)
 *
 * The device_secret is returned ONCE in the registration response and must be
 * stored securely on the device (e.g. in platform secure storage / Keychain).
 * It is never returned again after the initial registration call.
 *
 * Devices registered before HMAC authentication was introduced (device_secret = null)
 * are allowed through with a warning log until secrets are rotated.
 */
class LiteApiController extends Controller
{
    public function __construct(private readonly OpesCareLiteService $liteService) {}

    /**
     * POST /api/v1/lite/register-device
     *
     * Register a new Lite device for a facility.
     * Returns the device_secret ONCE — store it securely; it is never shown again.
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

        // Duplicate fingerprint guard — return existing device (without secret)
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
            facilityId:        $data['facility_id'],
            deviceName:        $data['device_name'],
            deviceFingerprint: $data['device_fingerprint'],
            authorizedBy:      $actorId,
            platform:          $data['platform'] ?? 'web',
            extraModules:      $data['extra_modules'] ?? [],
            offlineAllowed:    (bool) ($data['offline_allowed'] ?? false),
        );

        return response()->json([
            'message' => 'Device registered. Awaiting activation.',
            'device'  => [
                'id'            => $device->id,
                'status'        => $device->status,
                'config'        => $this->liteService->getConfig($device),
                // ONE-TIME secret — must be stored securely; never returned again
                'device_secret' => $device->device_secret,
            ],
        ], 201);
    }

    /**
     * GET /api/v1/lite/config
     *
     * Fetch current config for the authenticated device.
     */
    public function config(Request $request): JsonResponse
    {
        $device = $this->resolveDevice($request);
        if (!$device) {
            return response()->json(['message' => 'Device not found, not active, or invalid signature.'], 403);
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
            return response()->json(['message' => 'Device not found, not active, or invalid signature.'], 403);
        }

        $data = $request->validate([
            'events'               => 'required|array|min:1|max:200',
            'events.*.event_type'  => 'required|string|max:80',
            'events.*.client_id'   => 'required|string|max:64',
            'events.*.payload'     => 'required|array',
            'events.*.captured_at' => 'nullable|date',
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
            return response()->json(['message' => 'Device not found, not active, or invalid signature.'], 403);
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
            return response()->json(['message' => 'Device not found, not active, or invalid signature.'], 403);
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

    // ──────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Resolve and authenticate the device making the request.
     *
     * Authentication flow:
     * 1. Read X-Lite-Device-Id (or device_id query/body param).
     * 2. Load device from DB; verify it is active.
     * 3. If device has a device_secret → validate HMAC-SHA256 signature:
     *      message   = device_id . "." . timestamp . "." . sha256(request_body)
     *      signature = HMAC-SHA256(message, device_secret)
     *      Reject if timestamp is > 5 minutes old (replay protection).
     * 4. If device_secret is null (legacy/pre-auth device) → allow but warn.
     *
     * Returns null on any authentication failure.
     */
    private function resolveDevice(Request $request): ?LiteDevice
    {
        $deviceId = $request->header('X-Lite-Device-Id')
            ?? $request->query('device_id')
            ?? $request->input('device_id');

        if (! $deviceId) {
            return null;
        }

        $device = LiteDevice::find($deviceId);

        if (! $device || ! $device->isActive()) {
            return null;
        }

        // ── HMAC-SHA256 signature validation ──────────────────────────────────
        if ($device->device_secret !== null) {
            $timestamp = $request->header('X-Lite-Timestamp');
            $signature = $request->header('X-Lite-Signature');

            if (! $timestamp || ! $signature) {
                Log::warning('Lite device request missing HMAC headers', [
                    'device_id' => $deviceId,
                    'ip'        => $request->ip(),
                ]);
                return null;
            }

            // Replay protection: reject requests older than 5 minutes
            if (abs(time() - (int) $timestamp) > 300) {
                Log::warning('Lite device request timestamp out of window (possible replay)', [
                    'device_id' => $deviceId,
                    'timestamp' => $timestamp,
                    'ip'        => $request->ip(),
                ]);
                return null;
            }

            // Compute expected HMAC:
            //   message = device_id . "." . unix_timestamp . "." . sha256(raw_request_body)
            $bodyHash = hash('sha256', $request->getContent());
            $message  = $deviceId . '.' . $timestamp . '.' . $bodyHash;
            $expected = hash_hmac('sha256', $message, $device->device_secret);

            if (! hash_equals($expected, strtolower($signature))) {
                Log::warning('Lite device HMAC signature mismatch', [
                    'device_id' => $deviceId,
                    'ip'        => $request->ip(),
                ]);
                return null;
            }
        } else {
            // Legacy device without secret — allow but log for rotation tracking
            Log::warning('Lite device authenticated WITHOUT HMAC secret (legacy device — rotate secret)', [
                'device_id' => $deviceId,
                'ip'        => $request->ip(),
            ]);
        }

        return $device;
    }
}
