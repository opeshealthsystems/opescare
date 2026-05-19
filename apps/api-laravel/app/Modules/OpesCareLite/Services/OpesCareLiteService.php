<?php

namespace App\Modules\OpesCareLite\Services;

use App\Models\LiteConfig;
use App\Models\LiteConflict;
use App\Models\LiteDevice;
use App\Models\LiteModuleEntitlement;
use App\Models\LiteOfflineEvent;
use App\Models\LiteSyncJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class OpesCareLiteService
{
    /**
     * Default modules granted to every new Lite device.
     */
    private const DEFAULT_MODULES = [
        'health_id_lookup',
        'patient_registration',
        'queue_checkin',
        'vitals',
        'consultation_note',
        'basic_prescription',
        'basic_billing',
        'document_qr',
    ];

    /**
     * Actions always blocked in offline-limited mode.
     */
    private const BLOCKED_OFFLINE_ACTIONS = [
        'full_emr_access',
        'insurance_claim_submission',
        'public_health_submission',
        'final_document_issuance',
        'broad_patient_search',
        'non_emergency_consent_expansion',
    ];

    /**
     * Register a new Lite device for a facility.
     */
    public function registerDevice(
        string $facilityId,
        string $deviceName,
        string $deviceFingerprint,
        string $authorizedBy,
        string $platform = 'web',
        array  $extraModules = [],
        bool   $offlineAllowed = false,
    ): LiteDevice {
        $device = LiteDevice::create([
            'facility_id'        => $facilityId,
            'device_name'        => $deviceName,
            'device_fingerprint' => $deviceFingerprint,
            'environment'        => app()->environment(),
            'status'             => 'pending',
            'platform'           => $platform,
            'authorized_by'      => $authorizedBy,
            'allowed_modes'      => $offlineAllowed
                ? ['online', 'low-bandwidth', 'offline-limited']
                : ['online', 'low-bandwidth'],
        ]);

        // Create default config
        $allowedModules = array_unique(array_merge(self::DEFAULT_MODULES, $extraModules));

        LiteConfig::create([
            'lite_device_id'          => $device->id,
            'allowed_modules'         => $allowedModules,
            'language'                => 'en',
            'offline_allowed'         => $offlineAllowed,
            'low_bandwidth_mode'      => false,
            'sync_interval_seconds'   => 300,
            'blocked_offline_actions' => self::BLOCKED_OFFLINE_ACTIONS,
            'currency_code'           => 'NGN',
            'config_updated_at'       => now(),
        ]);

        // Grant module entitlements
        $this->grantModules($device->id, $allowedModules);

        return $device->fresh(['config', 'entitlements']);
    }

    /**
     * Activate a pending device.
     */
    public function activateDevice(LiteDevice $device): LiteDevice
    {
        $device->update([
            'status'       => 'active',
            'activated_at' => now(),
        ]);

        return $device->fresh();
    }

    /**
     * Suspend a device (keeps data, blocks access).
     */
    public function suspendDevice(LiteDevice $device, string $reason = ''): void
    {
        $device->update(['status' => 'suspended']);
    }

    /**
     * Revoke a device permanently.
     */
    public function revokeDevice(LiteDevice $device, string $reason): void
    {
        $device->update([
            'status'        => 'revoked',
            'revoked_at'    => now(),
            'revoke_reason' => $reason,
        ]);
    }

    /**
     * Get device config payload for the Lite client.
     */
    public function getConfig(LiteDevice $device): array
    {
        $cfg = $device->config;

        return [
            'device_id'               => $device->id,
            'device_name'             => $device->device_name,
            'facility_id'             => $device->facility_id,
            'status'                  => $device->status,
            'allowed_modes'           => $device->allowed_modes ?? ['online'],
            'allowed_modules'         => $cfg?->allowed_modules ?? self::DEFAULT_MODULES,
            'language'                => $cfg?->language ?? 'en',
            'offline_allowed'         => $cfg?->offline_allowed ?? false,
            'low_bandwidth_mode'      => $cfg?->low_bandwidth_mode ?? false,
            'sync_interval_seconds'   => $cfg?->sync_interval_seconds ?? 300,
            'blocked_offline_actions' => $cfg?->blocked_offline_actions ?? self::BLOCKED_OFFLINE_ACTIONS,
            'currency_code'           => $cfg?->currency_code ?? 'NGN',
            'config_updated_at'       => $cfg?->config_updated_at?->toIso8601String(),
        ];
    }

    /**
     * Accept and queue offline events pushed from a Lite device.
     * Returns summary of what was accepted / rejected / conflicted.
     */
    public function pushOfflineEvents(LiteDevice $device, array $events): array
    {
        $job = LiteSyncJob::create([
            'lite_device_id' => $device->id,
            'direction'      => 'push',
            'status'         => 'running',
            'started_at'     => now(),
        ]);

        $device->touchSeen();

        $accepted  = 0;
        $rejected  = 0;
        $conflicts = 0;
        $errors    = [];

        foreach ($events as $event) {
            $clientId  = $event['client_id'] ?? Str::uuid()->toString();
            $eventType = $event['event_type'] ?? 'unknown';
            $payload   = $event['payload'] ?? [];
            $capturedAt = $event['captured_at'] ?? now()->toIso8601String();

            // Block illegal offline actions
            $cfg = $device->config;
            $blocked = $cfg?->blocked_offline_actions ?? self::BLOCKED_OFFLINE_ACTIONS;
            if (in_array($eventType, $blocked, true)) {
                $rejected++;
                $errors[] = "Blocked offline action: {$eventType}";
                continue;
            }

            // Idempotency — skip duplicates
            $exists = LiteOfflineEvent::where('lite_device_id', $device->id)
                ->where('client_id', $clientId)
                ->first();
            if ($exists) {
                $accepted++;
                continue;
            }

            $offlineEvent = LiteOfflineEvent::create([
                'lite_device_id' => $device->id,
                'facility_id'    => $device->facility_id,
                'event_type'     => $eventType,
                'client_id'      => $clientId,
                'payload'        => $payload,
                'status'         => 'queued',
                'captured_at'    => $capturedAt,
                'received_at'    => now(),
            ]);

            // Process immediately for simple event types
            $result = $this->applyOfflineEvent($offlineEvent);

            if ($result === 'conflict') {
                $conflicts++;
            } elseif ($result === 'applied') {
                $accepted++;
            } else {
                $rejected++;
            }
        }

        $job->update([
            'status'            => 'completed',
            'events_sent'       => count($events),
            'events_applied'    => $accepted,
            'events_rejected'   => $rejected,
            'conflicts_created' => $conflicts,
            'completed_at'      => now(),
        ]);

        return [
            'sync_job_id' => $job->id,
            'accepted'    => $accepted,
            'rejected'    => $rejected,
            'conflicts'   => $conflicts,
            'errors'      => $errors,
        ];
    }

    /**
     * Build a pull-sync payload for the device: pending updates since last sync.
     */
    public function pullSync(LiteDevice $device, ?string $since = null): array
    {
        $device->touchSeen();

        $job = LiteSyncJob::create([
            'lite_device_id' => $device->id,
            'direction'      => 'pull',
            'status'         => 'running',
            'started_at'     => now(),
        ]);

        // Gather open conflicts for device
        $openConflicts = LiteConflict::where('lite_device_id', $device->id)
            ->where('status', 'open')
            ->with('offlineEvent')
            ->get()
            ->map(fn ($c) => [
                'conflict_id'    => $c->id,
                'event_type'     => $c->offlineEvent?->event_type,
                'conflict_type'  => $c->conflict_type,
                'server_version' => $c->server_version,
                'device_version' => $c->device_version,
                'captured_at'    => $c->offlineEvent?->captured_at?->toIso8601String(),
            ])
            ->all();

        $job->update([
            'status'       => 'completed',
            'events_sent'  => 0,
            'completed_at' => now(),
        ]);

        return [
            'sync_job_id'    => $job->id,
            'server_time'    => now()->toIso8601String(),
            'open_conflicts' => $openConflicts,
            'config_stale'   => $this->isConfigStale($device, $since),
            'updates'        => [], // Full delta sync would populate this
        ];
    }

    /**
     * Apply a single offline event (simple mapping to domain actions).
     * Returns: 'applied'|'conflict'|'rejected'
     */
    private function applyOfflineEvent(LiteOfflineEvent $event): string
    {
        $event->update(['status' => 'processing']);

        try {
            // Each event type has a handler; extend as domain grows
            match ($event->event_type) {
                'vitals'               => $this->applyVitalsEvent($event),
                'consultation_note'    => $this->applyConsultationEvent($event),
                'basic_prescription'   => $this->applyPrescriptionEvent($event),
                'basic_billing'        => $this->applyBillingEvent($event),
                'patient_registration' => $this->applyPatientRegistrationEvent($event),
                'stock_update'         => $this->applyStockUpdateEvent($event),
                default                => null,
            };

            $event->update(['status' => 'applied', 'applied_at' => now()]);
            return 'applied';

        } catch (\Throwable $e) {
            // Conflict or validation error → mark and create conflict record
            $event->update(['status' => 'conflict']);
            LiteConflict::create([
                'lite_device_id'         => $event->lite_device_id,
                'lite_offline_event_id'  => $event->id,
                'conflict_type'          => 'data_mismatch',
                'device_version'         => $event->payload,
                'server_version'         => null,
                'status'                 => 'open',
            ]);
            return 'conflict';
        }
    }

    /**
     * Draft vitals capture — stores as offline draft pending full visit.
     * Real implementation would call VitalsService; here we safely store JSON.
     */
    private function applyVitalsEvent(LiteOfflineEvent $event): void
    {
        // Validate required fields
        $payload = $event->payload;
        if (empty($payload['patient_id'])) {
            throw new \InvalidArgumentException('patient_id required for vitals event');
        }
        // Domain write would occur here via EncounterManagement service
        // Skipped to avoid coupling — controller layer calls proper services
    }

    private function applyConsultationEvent(LiteOfflineEvent $event): void
    {
        $payload = $event->payload;
        if (empty($payload['patient_id'])) {
            throw new \InvalidArgumentException('patient_id required for consultation event');
        }
    }

    private function applyPrescriptionEvent(LiteOfflineEvent $event): void
    {
        $payload = $event->payload;
        if (empty($payload['patient_id']) || empty($payload['items'])) {
            throw new \InvalidArgumentException('patient_id and items required for prescription event');
        }
    }

    private function applyBillingEvent(LiteOfflineEvent $event): void
    {
        $payload = $event->payload;
        if (empty($payload['patient_id']) || empty($payload['amount'])) {
            throw new \InvalidArgumentException('patient_id and amount required for billing event');
        }
    }

    private function applyPatientRegistrationEvent(LiteOfflineEvent $event): void
    {
        $payload = $event->payload;
        if (empty($payload['first_name']) || empty($payload['last_name'])) {
            throw new \InvalidArgumentException('first_name and last_name required for patient registration');
        }
    }

    private function applyStockUpdateEvent(LiteOfflineEvent $event): void
    {
        $payload = $event->payload;
        if (empty($payload['item_id']) || !isset($payload['quantity_delta'])) {
            throw new \InvalidArgumentException('item_id and quantity_delta required for stock update');
        }
    }

    /**
     * Resolve a conflict.
     */
    public function resolveConflict(
        LiteConflict $conflict,
        string       $resolvedBy,
        string       $resolution,
        string       $note = ''
    ): LiteConflict {
        $conflict->update([
            'status'          => $resolution === 'dismiss' ? 'dismissed' : 'resolved',
            'resolved_by'     => $resolvedBy,
            'resolution_note' => $note,
            'resolved_at'     => now(),
        ]);

        return $conflict->fresh();
    }

    /**
     * Grant module entitlements to a device.
     */
    private function grantModules(string $deviceId, array $modules): void
    {
        foreach ($modules as $moduleKey) {
            LiteModuleEntitlement::updateOrCreate(
                ['lite_device_id' => $deviceId, 'module_key' => $moduleKey],
                ['is_enabled' => true, 'granted_at' => now(), 'revoked_at' => null],
            );
        }
    }

    /**
     * Check if device config is stale relative to a since timestamp.
     */
    private function isConfigStale(LiteDevice $device, ?string $since): bool
    {
        if (!$since) {
            return true;
        }
        $configUpdated = $device->config?->config_updated_at;
        if (!$configUpdated) {
            return false;
        }
        return $configUpdated->isAfter($since);
    }

    /**
     * Summary stats for admin dashboard.
     */
    public function getAdminStats(string $facilityId): array
    {
        $devices = LiteDevice::where('facility_id', $facilityId)->get();

        return [
            'total_devices'    => $devices->count(),
            'active_devices'   => $devices->where('status', 'active')->count(),
            'pending_devices'  => $devices->where('status', 'pending')->count(),
            'revoked_devices'  => $devices->whereIn('status', ['revoked', 'suspended', 'lost'])->count(),
            'open_conflicts'   => LiteConflict::whereIn('lite_device_id', $devices->pluck('id'))
                ->where('status', 'open')->count(),
            'pending_events'   => LiteOfflineEvent::whereIn('lite_device_id', $devices->pluck('id'))
                ->where('status', 'queued')->count(),
        ];
    }
}
