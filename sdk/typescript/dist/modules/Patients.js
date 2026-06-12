"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Patients = void 0;
class Patients {
    constructor(client) {
        this.client = client;
    }
    getSummary(healthId) {
        return this.client.get(`api/v1/connect/patients/${healthId}/summary`);
    }
    getEmergencyProfile(healthId) {
        return this.client.get(`api/v1/connect/patients/${healthId}/emergency-profile`);
    }
    search(params) {
        return this.client.post('api/v1/connect/patients/search', params);
    }
}
exports.Patients = Patients;
//# sourceMappingURL=Patients.js.map