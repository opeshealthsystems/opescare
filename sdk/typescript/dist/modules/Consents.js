"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Consents = void 0;
class Consents {
    constructor(client) {
        this.client = client;
    }
    request(healthId, params) {
        return this.client.post('api/v1/connect/consents/request', { health_id: healthId, ...params });
    }
    verify(healthId, scope) {
        return this.client.post('api/v1/connect/consents/verify', { health_id: healthId, scope });
    }
    requestEmergencyAccess(healthId, reason, emergencyType = 'clinical_emergency') {
        return this.client.post('api/v1/connect/emergency-access/request', {
            health_id: healthId,
            reason,
            emergency_type: emergencyType,
        });
    }
}
exports.Consents = Consents;
//# sourceMappingURL=Consents.js.map