"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
exports.Records = void 0;
const crypto_1 = require("crypto");
function genKey(prefix) {
    return `${prefix}-${(0, crypto_1.randomBytes)(16).toString('hex')}`;
}
class Records {
    constructor(client) {
        this.client = client;
    }
    pushEncounter(data, idempotencyKey) {
        return this.client.post('api/v1/connect/records/encounters', data, idempotencyKey ?? genKey('enc'));
    }
    pushLabResult(data, idempotencyKey) {
        return this.client.post('api/v1/connect/records/lab-results', data, idempotencyKey ?? genKey('lab'));
    }
    pushPrescription(data, idempotencyKey) {
        return this.client.post('api/v1/connect/records/prescriptions', data, idempotencyKey ?? genKey('rx'));
    }
}
exports.Records = Records;
//# sourceMappingURL=Records.js.map