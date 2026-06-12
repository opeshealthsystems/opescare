export declare class ApiClient {
    private accessToken;
    private readonly http;
    constructor(baseUrl: string, accessToken: string, timeout?: number);
    get<T = Record<string, unknown>>(path: string, params?: Record<string, unknown>): Promise<T>;
    post<T = Record<string, unknown>>(path: string, data?: Record<string, unknown>, idempotencyKey?: string): Promise<T>;
    delete<T = Record<string, unknown>>(path: string): Promise<T>;
    private request;
    private mapError;
}
//# sourceMappingURL=ApiClient.d.ts.map