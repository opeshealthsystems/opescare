import { ApiClient } from '../http/ApiClient';
export interface ConsentRequestParams {
    purpose: string;
    requested_scopes: string[];
    validity_period_days?: number;
    system_name?: string;
}
export declare class Consents {
    private readonly client;
    constructor(client: ApiClient);
    request(healthId: string, params: ConsentRequestParams): Promise<Record<string, unknown>>;
    verify(healthId: string, scope: string): Promise<Record<string, unknown>>;
    requestEmergencyAccess(healthId: string, reason: string, emergencyType?: string): Promise<Record<string, unknown>>;
}
//# sourceMappingURL=Consents.d.ts.map