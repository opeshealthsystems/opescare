import { ApiClient } from '../http/ApiClient';

export interface ConsentRequestParams {
  purpose: string;
  requested_scopes: string[];
  validity_period_days?: number;
  system_name?: string;
}

export class Consents {
  constructor(private readonly client: ApiClient) {}

  request(healthId: string, params: ConsentRequestParams): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/consents/request', { health_id: healthId, ...params });
  }

  verify(healthId: string, scope: string): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/consents/verify', { health_id: healthId, scope });
  }

  requestEmergencyAccess(healthId: string, reason: string, emergencyType = 'clinical_emergency'): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/emergency-access/request', {
      health_id:      healthId,
      reason,
      emergency_type: emergencyType,
    });
  }
}
