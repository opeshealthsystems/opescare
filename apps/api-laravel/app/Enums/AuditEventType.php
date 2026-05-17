<?php

namespace App\Enums;

enum AuditEventType: string
{
    case HEALTH_ID_CREATED = 'health_id_created';
    case HEALTH_ID_VERIFIED = 'health_id_verified';
    case HEALTH_ID_LOOKUP = 'health_id_lookup';
    case HEALTH_ID_QR_SCANNED = 'health_id_qr_scanned';
    case HEALTH_ID_QR_TOKEN_CREATED = 'health_id_qr_token_created';
    case HEALTH_ID_QR_TOKEN_REVOKED = 'health_id_qr_token_revoked';
    case HEALTH_ID_STATUS_CHANGED = 'health_id_status_changed';
    case HEALTH_ID_DUPLICATE_SUSPECTED = 'health_id_duplicate_suspected';
    case HEALTH_ID_MERGED = 'health_id_merged';
    case HEALTH_ID_UNMERGED = 'health_id_unmerged';
    case TEMPORARY_CONSENT_QR_CREATED = 'temporary_consent_qr_created';
    case TEMPORARY_CONSENT_QR_USED = 'temporary_consent_qr_used';
    case TEMPORARY_CONSENT_QR_EXPIRED = 'temporary_consent_qr_expired';
    case EMERGENCY_PROFILE_ACCESSED = 'emergency_profile_accessed';
    case EXTERNAL_SYSTEM_VERIFIED_HEALTH_ID = 'external_system_verified_health_id';
    case MEDICAL_ID_ACCESS_DENIED = 'medical_id_access_denied';
    case IDENTITY_PROFILE_UPDATED = 'identity_profile_updated';
}
