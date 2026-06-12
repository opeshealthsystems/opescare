import { ApiClient } from '../http/ApiClient';

export interface ResolveParams {
  health_id?: string;
  first_name?: string;
  last_name?: string;
  date_of_birth?: string;
}

export interface ResolveResult {
  status: 'found' | 'created' | 'not_found';
  health_id?: string;
  patient?: Record<string, unknown>;
  next_action?: string;
}

export class HealthIds {
  constructor(private readonly client: ApiClient) {}

  resolve(params: ResolveParams): Promise<ResolveResult> {
    return this.client.post('api/v1/connect/patients/resolve', params as Record<string, unknown>);
  }

  verify(healthId: string): Promise<Record<string, unknown>> {
    return this.client.get(`api/v1/connect/patients/verify/${healthId}`);
  }
}
