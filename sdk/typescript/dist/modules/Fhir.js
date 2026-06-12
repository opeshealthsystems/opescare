"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Fhir = void 0;
class Fhir {
    constructor(client) {
        this.client = client;
        this.metadata = () => this.client.get('api/fhir/R4/metadata');
        this.patient = (healthId) => this.client.get(`api/fhir/R4/Patient/${healthId}`);
        this.searchPatients = (params = {}) => this.client.get('api/fhir/R4/Patient', params);
        this.patientEverything = (healthId) => this.client.get(`api/fhir/R4/Patient/${healthId}/$everything`);
        this.allergyIntolerances = (healthId, params = {}) => this.client.get('api/fhir/R4/AllergyIntolerance', { patient: healthId, ...params });
        this.medicationRequests = (healthId, params = {}) => this.client.get('api/fhir/R4/MedicationRequest', { patient: healthId, ...params });
        this.diagnosticReports = (healthId, params = {}) => this.client.get('api/fhir/R4/DiagnosticReport', { patient: healthId, ...params });
        this.conditions = (healthId, params = {}) => this.client.get('api/fhir/R4/Condition', { patient: healthId, ...params });
        this.immunizations = (healthId, params = {}) => this.client.get('api/fhir/R4/Immunization', { patient: healthId, ...params });
        this.encounters = (healthId, params = {}) => this.client.get('api/fhir/R4/Encounter', { patient: healthId, ...params });
    }
}
exports.Fhir = Fhir;
//# sourceMappingURL=Fhir.js.map