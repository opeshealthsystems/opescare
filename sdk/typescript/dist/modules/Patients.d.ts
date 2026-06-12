import { ApiClient } from '../http/ApiClient';
export declare class Patients {
    private readonly client;
    constructor(client: ApiClient);
    getSummary(healthId: string): Promise<Record<string, unknown>>;
    getEmergencyProfile(healthId: string): Promise<Record<string, unknown>>;
    search(params: Record<string, unknown>): Promise<Record<string, unknown>>;
}
//# sourceMappingURL=Patients.d.ts.map