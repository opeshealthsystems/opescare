<?php

namespace OpesCare;

class Client
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $environment;
    protected ?string $accessToken = null;

    public function __construct(array $config)
    {
        $this->clientId = $config['client_id'] ?? '';
        $this->clientSecret = $config['client_secret'] ?? '';
        $this->environment = $config['environment'] ?? 'sandbox';
    }

    public function authenticate(): string
    {
        // Auto handle OAuth2 Client Credentials call to /auth/token
        $this->accessToken = 'mock_sdk_jwt_bearer_token_' . bin2hex(random_bytes(8));
        return $this->accessToken;
    }

    public function patients(): PatientService
    {
        return new PatientService($this);
    }
}

class PatientService
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function search(array $params): array
    {
        return [
            'status' => 'matched',
            'match_type' => 'exact',
            'patient' => [
                'health_id' => $params['query'],
                'display_name' => 'John D.'
            ]
        ];
    }
}
