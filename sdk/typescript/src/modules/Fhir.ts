import { ApiClient } from '../http/ApiClient';

export class Fhir {
  constructor(private readonly client: ApiClient) {}

  metadata = (): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/metadata');

  patient = (healthId: string): Promise<Record<string, unknown>> =>
    this.client.get(`api/fhir/R4/Patient/${healthId}`);

  searchPatients = (params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/Patient', params);

  patientEverything = (healthId: string): Promise<Record<string, unknown>> =>
    this.client.get(`api/fhir/R4/Patient/${healthId}/$everything`);

  allergyIntolerances = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/AllergyIntolerance', { patient: healthId, ...params });

  medicationRequests = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/MedicationRequest', { patient: healthId, ...params });

  diagnosticReports = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/DiagnosticReport', { patient: healthId, ...params });

  conditions = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/Condition', { patient: healthId, ...params });

  immunizations = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/Immunization', { patient: healthId, ...params });

  encounters = (healthId: string, params: Record<string, unknown> = {}): Promise<Record<string, unknown>> =>
    this.client.get('api/fhir/R4/Encounter', { patient: healthId, ...params });
}
