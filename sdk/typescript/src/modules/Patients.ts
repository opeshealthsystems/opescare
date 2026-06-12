import { ApiClient } from '../http/ApiClient';

export class Patients {
  constructor(private readonly client: ApiClient) {}

  getSummary(healthId: string): Promise<Record<string, unknown>> {
    return this.client.get(`api/v1/connect/patients/${healthId}/summary`);
  }

  getEmergencyProfile(healthId: string): Promise<Record<string, unknown>> {
    return this.client.get(`api/v1/connect/patients/${healthId}/emergency-profile`);
  }

  search(params: Record<string, unknown>): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/patients/search', params);
  }
}
