import { ApiClient } from '../http/ApiClient';
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
export declare class Records {
    private readonly client;
    constructor(client: ApiClient);
    pushEncounter(data: EncounterData, idempotencyKey?: string): Promise<Record<string, unknown>>;
    pushLabResult(data: LabResultData, idempotencyKey?: string): Promise<Record<string, unknown>>;
    pushPrescription(data: PrescriptionData, idempotencyKey?: string): Promise<Record<string, unknown>>;
}
//# sourceMappingURL=Records.d.ts.map