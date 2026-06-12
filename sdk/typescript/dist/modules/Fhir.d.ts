import { ApiClient } from '../http/ApiClient';
export declare class Fhir {
    private readonly client;
    constructor(client: ApiClient);
    metadata: () => Promise<Record<string, unknown>>;
    patient: (healthId: string) => Promise<Record<string, unknown>>;
    searchPatients: (params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    patientEverything: (healthId: string) => Promise<Record<string, unknown>>;
    allergyIntolerances: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    medicationRequests: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    diagnosticReports: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    conditions: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    immunizations: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
    encounters: (healthId: string, params?: Record<string, unknown>) => Promise<Record<string, unknown>>;
}
//# sourceMappingURL=Fhir.d.ts.map