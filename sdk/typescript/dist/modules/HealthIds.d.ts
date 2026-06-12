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
export declare class HealthIds {
    private readonly client;
    constructor(client: ApiClient);
    resolve(params: ResolveParams): Promise<ResolveResult>;
    verify(healthId: string): Promise<Record<string, unknown>>;
}
//# sourceMappingURL=HealthIds.d.ts.map