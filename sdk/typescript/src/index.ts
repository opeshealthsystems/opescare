import { TokenManager } from './auth/TokenManager';
import { ApiClient }    from './http/ApiClient';
import { Consents }     from './modules/Consents';
import { Fhir }         from './modules/Fhir';
import { HealthIds }    from './modules/HealthIds';
import { Patients }     from './modules/Patients';
import { Records }      from './modules/Records';
import { Webhooks }     from './modules/Webhooks';

export * from './errors';
export { HealthIds, Patients, Consents, Records, Fhir, Webhooks };

const BASE_URLS: Record<string, string> = {
  sandbox:    'http://opescare.test',
  production: 'https://api.opescare.com',
};

export interface OpesCareClientOptions {
  clientId:     string;
  clientSecret: string;
  environment?: 'sandbox' | 'production';
  baseUrl?:     string;
  timeout?:     number;
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
export class OpesCareClient {
  readonly healthIds: HealthIds;
  readonly patients:  Patients;
  readonly consents:  Consents;
  readonly records:   Records;
  readonly fhir:      Fhir;
  readonly webhooks:  Webhooks;

  private constructor(
    private readonly options: Required<OpesCareClientOptions>,
    token: string
  ) {
    const http        = new ApiClient(options.baseUrl, token, options.timeout);
    this.healthIds    = new HealthIds(http);
    this.patients     = new Patients(http);
    this.consents     = new Consents(http);
    this.records      = new Records(http);
    this.fhir         = new Fhir(http);
    this.webhooks     = new Webhooks(http);
  }

  /**
   * Create a client and fetch the initial access token.
   * Throws AuthenticationError if credentials are invalid.
   */
  static async create(options: OpesCareClientOptions): Promise<OpesCareClient> {
    const env     = options.environment ?? 'sandbox';
    const baseUrl = options.baseUrl ?? BASE_URLS[env]
      ?? (() => { throw new Error(`Unknown environment "${env}".`); })();

    const tm    = new TokenManager(baseUrl, options.clientId, options.clientSecret);
    const token = await tm.getToken();

    const resolved: Required<OpesCareClientOptions> = {
      ...options,
      environment: env as 'sandbox' | 'production',
      baseUrl,
      timeout: options.timeout ?? 30000,
    };

    return new OpesCareClient(resolved, token);
  }

  /** Create a fresh client with a new token (call if you receive a 401). */
  refresh(): Promise<OpesCareClient> {
    return OpesCareClient.create(this.options);
  }
}
