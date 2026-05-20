<?php

namespace App\Services;

use App\Models\AuditEvent;
use Illuminate\Http\Request;

class AuditLogger
{
    /**
     * Log a clinical interoperability access event to PostgreSQL.
     */
    public static function log(
        Request $request,
        string $actionType,
        string $resourceType,
        ?string $resourceId,
        ?string $patientId = null,
        bool $emergencyOverride = false,
        ?string $reason = null,
        array $beforeState = [],
        array $afterState = []
    ): AuditEvent {
        $clientId   = $request->attributes->get('integration_client_id');
        $facilityId = $request->attributes->get('facility_id');

        // actor_id is a UUID column — integration client IDs are strings, not UUIDs.
        // Store null for actor_id and record the client identifier in actor_role.
        $actorRole = $clientId ? 'integration_client:' . $clientId : 'integration_client';

        return AuditEvent::create([
            'actor_id'   => null,   // no user UUID for machine-to-machine calls
            'actor_role' => $actorRole,
            'facility_id' => $facilityId,
            'patient_id' => $patientId,
            'action_type' => $actionType,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'consent_grant_id' => $request->header('X-Consent-Grant-Id'),
            'emergency_override' => $emergencyOverride,
            'source_system' => 'opescare_connect',
            'ip_address' => $request->ip(),
            'reason' => $reason ?? $request->header('X-Emergency-Reason'),
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'created_at' => now()
        ]);
    }
}
