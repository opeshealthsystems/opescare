"use strict";
var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __exportStar = (this && this.__exportStar) || function(m, exports) {
    for (var p in m) if (p !== "default" && !Object.prototype.hasOwnProperty.call(exports, p)) __createBinding(exports, m, p);
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.OpesCareClient = exports.Webhooks = exports.Fhir = exports.Records = exports.Consents = exports.Patients = exports.HealthIds = void 0;
const TokenManager_1 = require("./auth/TokenManager");
const ApiClient_1 = require("./http/ApiClient");
const Consents_1 = require("./modules/Consents");
Object.defineProperty(exports, "Consents", { enumerable: true, get: function () { return Consents_1.Consents; } });
const Fhir_1 = require("./modules/Fhir");
Object.defineProperty(exports, "Fhir", { enumerable: true, get: function () { return Fhir_1.Fhir; } });
const HealthIds_1 = require("./modules/HealthIds");
Object.defineProperty(exports, "HealthIds", { enumerable: true, get: function () { return HealthIds_1.HealthIds; } });
const Patients_1 = require("./modules/Patients");
Object.defineProperty(exports, "Patients", { enumerable: true, get: function () { return Patients_1.Patients; } });
const Records_1 = require("./modules/Records");
Object.defineProperty(exports, "Records", { enumerable: true, get: function () { return Records_1.Records; } });
const Webhooks_1 = require("./modules/Webhooks");
Object.defineProperty(exports, "Webhooks", { enumerable: true, get: function () { return Webhooks_1.Webhooks; } });
__exportStar(require("./errors"), exports);
const BASE_URLS = {
    sandbox: 'http://opescare.test',
    production: 'https://api.opescare.com',
};
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
class OpesCareClient {
    constructor(options, token) {
        this.options = options;
        const http = new ApiClient_1.ApiClient(options.baseUrl, token, options.timeout);
        this.healthIds = new HealthIds_1.HealthIds(http);
        this.patients = new Patients_1.Patients(http);
        this.consents = new Consents_1.Consents(http);
        this.records = new Records_1.Records(http);
        this.fhir = new Fhir_1.Fhir(http);
        this.webhooks = new Webhooks_1.Webhooks(http);
    }
    /**
     * Create a client and fetch the initial access token.
     * Throws AuthenticationError if credentials are invalid.
     */
    static async create(options) {
        const env = options.environment ?? 'sandbox';
        const baseUrl = options.baseUrl ?? BASE_URLS[env]
            ?? (() => { throw new Error(`Unknown environment "${env}".`); })();
        const tm = new TokenManager_1.TokenManager(baseUrl, options.clientId, options.clientSecret);
        const token = await tm.getToken();
        const resolved = {
            ...options,
            environment: env,
            baseUrl,
            timeout: options.timeout ?? 30000,
        };
        return new OpesCareClient(resolved, token);
    }
    /** Create a fresh client with a new token (call if you receive a 401). */
    refresh() {
        return OpesCareClient.create(this.options);
    }
}
exports.OpesCareClient = OpesCareClient;
//# sourceMappingURL=index.js.map