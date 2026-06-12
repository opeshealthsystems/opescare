import { ApiClient } from '../http/ApiClient';
import { randomBytes } from 'crypto';

function genKey(prefix: string): string {
  return `${prefix}-${randomBytes(16).toString('hex')}`;
}

export interface EncounterData {
  health_id: string;
  encounter_type: string;
  clinical_note: string;
  severity?: string;
  alert_type?: string;
  cdss_rule_id?: string;
  confidence_score?: number;
  occurred_at?: string;
  source_system?: string;
  [key: string]: unknown;
}

export interface LabResultData {
  health_id: string;
  test_name: string;
  result_value: string;
  reference_range?: string;
  interpretation?: string;
  flagged?: boolean;
  flag_level?: string;
  occurred_at?: string;
  source_system?: string;
  [key: string]: unknown;
}

export interface PrescriptionData {
  health_id: string;
  alert_type?: string;
  medication_name?: string;
  contraindication_reason?: string;
  severity?: string;
  recommendation?: string;
  occurred_at?: string;
  source_system?: string;
  [key: string]: unknown;
}

export class Records {
  constructor(private readonly client: ApiClient) {}

  pushEncounter(data: EncounterData, idempotencyKey?: string): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/records/encounters', data as Record<string, unknown>, idempotencyKey ?? genKey('enc'));
  }

  pushLabResult(data: LabResultData, idempotencyKey?: string): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/records/lab-results', data as Record<string, unknown>, idempotencyKey ?? genKey('lab'));
  }

  pushPrescription(data: PrescriptionData, idempotencyKey?: string): Promise<Record<string, unknown>> {
    return this.client.post('api/v1/connect/records/prescriptions', data as Record<string, unknown>, idempotencyKey ?? genKey('rx'));
  }
}
