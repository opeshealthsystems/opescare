import { Consents } from './modules/Consents';
import { Fhir } from './modules/Fhir';
import { HealthIds } from './modules/HealthIds';
import { Patients } from './modules/Patients';
import { Records } from './modules/Records';
import { Webhooks } from './modules/Webhooks';
export * from './errors';
export { HealthIds, Patients, Consents, Records, Fhir, Webhooks };
export interface OpesCareClientOptions {
    clientId: string;
    clientSecret: string;
    environment?: 'sandbox' | 'production';
    baseUrl?: string;
    timeout?: number;
}
/**
 * OpesCare Connect Suite TypeScript SDK
 *
 * @example
 * ```typescript
 * import { OpesCareClient } from '@opescare/sdk';
 *
 * const client = await OpesCareClient.create({
 *   clientId:     'sandbox_xxxxxxxxxxxx',
 *   clientSecret: 'sk_sandbox_xxxxxxxxxxxx',
 *   environment:  'sandbox',
 * });
 *
 * // Read allergies (FHIR R4)
 * const allergies = await client.fhir.allergyIntolerances('CM-HID-7KQ9-MP42-X8D1');
 *
 * // Push a CDSS recommendation
 * await client.records.pushEncounter({
 *   health_id:      'CM-HID-7KQ9-MP42-X8D1',
 *   encounter_type: 'cdss_alert',
 *   clinical_note:  'Drug interaction detected: Warfarin + Aspirin',
 *   severity:       'high',
 * });
 * ```
 */
export declare class OpesCareClient {
    private readonly options;
    readonly healthIds: HealthIds;
    readonly patients: Patients;
    readonly consents: Consents;
    readonly records: Records;
    readonly fhir: Fhir;
    readonly webhooks: Webhooks;
    private constructor();
    /**
     * Create a client and fetch the initial access token.
     * Throws AuthenticationError if credentials are invalid.
     */
    static create(options: OpesCareClientOptions): Promise<OpesCareClient>;
    /** Create a fresh client with a new token (call if you receive a 401). */
    refresh(): Promise<OpesCareClient>;
}
//# sourceMappingURL=index.d.ts.map