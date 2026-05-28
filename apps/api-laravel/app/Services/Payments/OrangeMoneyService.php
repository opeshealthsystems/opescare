<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Orange Money (Cameroon) Payment Service
 *
 * Uses Orange Money Web Payment API.
 * Docs: https://developer.orange.com/apis/om-webpay-prod/getting-started
 *
 * Env: ORANGE_MONEY_BASE_URL, ORANGE_MONEY_CLIENT_ID, ORANGE_MONEY_CLIENT_SECRET,
 *      ORANGE_MONEY_MERCHANT_KEY, ORANGE_MONEY_CURRENCY, ORANGE_MONEY_RETURN_URL,
 *      ORANGE_MONEY_CANCEL_URL, ORANGE_MONEY_NOTIF_URL
 */
class OrangeMoneyService implements PaymentProvider
{
    private string $baseUrl;
    private string $clientId;
    private string $clientSecret;
    private string $merchantKey;
    private string $currency;
    private string $returnUrl;
    private string $cancelUrl;
    private string $notifUrl;

    public function __construct()
    {
        $this->baseUrl      = rtrim(config('services.orange_money.base_url', 'https://api.orange.com'), '/');
        $this->clientId     = config('services.orange_money.client_id', '');
        $this->clientSecret = config('services.orange_money.client_secret', '');
        $this->merchantKey  = config('services.orange_money.merchant_key', '');
        $this->currency     = config('services.orange_money.currency', 'XAF');
        $this->returnUrl    = config('services.orange_money.return_url', '');
        $this->cancelUrl    = config('services.orange_money.cancel_url', '');
        $this->notifUrl     = config('services.orange_money.notif_url', '');
    }

    public function getName(): string
    {
        return 'orange_money';
    }

    public function requestPayment(
        string $phoneNumber,
        float  $amount,
        string $currency,
        string $externalRef,
        string $description
    ): array {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['success' => false, 'error' => 'Failed to obtain Orange Money access token'];
        }

        try {
            $response = Http::withToken($token)
                ->post("{$this->baseUrl}/orange-money-webpay/cm/v1/webpayment", [
                    'merchant_key'   => $this->merchantKey,
                    'currency'       => $currency ?: $this->currency,
                    'order_id'       => $externalRef,
                    'amount'         => intval($amount),
                    'return_url'     => $this->returnUrl,
                    'cancel_url'     => $this->cancelUrl,
                    'notif_url'      => $this->notifUrl,
                    'lang'           => 'fr',
                    'reference'      => $externalRef,
                ]);

            if ($response->ok()) {
                $data = $response->json();
                return [
                    'success'        => true,
                    'reference_id'   => $data['pay_token'] ?? $externalRef,
                    'payment_url'    => $data['payment_url'] ?? null,
                    'status'         => 'pending',
                    'raw'            => $data,
                ];
            }

            return ['success' => false, 'error' => "Orange API error: HTTP {$response->status()}"];

        } catch (\Throwable $e) {
            Log::error('Orange Money exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function checkStatus(string $transactionRef): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return ['status' => 'UNKNOWN', 'error' => 'Token error'];
        }

        try {
            $response = Http::withToken($token)
                ->get("{$this->baseUrl}/orange-money-webpay/cm/v1/transactionstatus", [
                    'order_id' => $transactionRef,
                ]);

            if ($response->ok()) {
                $data = $response->json();
                return [
                    'status'   => $data['status'] ?? 'UNKNOWN',
                    'amount'   => $data['amount'] ?? null,
                    'currency' => $data['currency'] ?? null,
                    'raw'      => $data,
                ];
            }

            return ['status' => 'UNKNOWN', 'error' => "HTTP {$response->status()}"];

        } catch (\Throwable $e) {
            return ['status' => 'UNKNOWN', 'error' => $e->getMessage()];
        }
    }

    private function getAccessToken(): ?string
    {
        try {
            $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
                ->asForm()
                ->post("{$this->baseUrl}/oauth/v3/token", ['grant_type' => 'client_credentials']);

            return $response->ok() ? $response->json('access_token') : null;

        } catch (\Throwable) {
            return null;
        }
    }
}
