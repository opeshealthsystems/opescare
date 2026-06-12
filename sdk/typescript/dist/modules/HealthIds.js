"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.HealthIds = void 0;
class HealthIds {
    constructor(client) {
        this.client = client;
    }
    resolve(params) {
        return this.client.post('api/v1/connect/patients/resolve', params);
    }
    verify(healthId) {
        return this.client.get(`api/v1/connect/patients/verify/${healthId}`);
    }
}
exports.HealthIds = HealthIds;
//# sourceMappingURL=HealthIds.js.map