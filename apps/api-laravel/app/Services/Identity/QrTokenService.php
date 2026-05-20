<?php

namespace App\Services\Identity;

use Illuminate\Support\Str;
use App\Models\HealthIdQrToken;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class QrTokenService
{
    /**
     * Generate a new QR token payload for a patient
     * 
     * @param string $patientId
     * @param string $type e.g. static_identity_qr, temporary_consent_qr
     * @param int|null $expiresInMinutes
     * @return array [raw_token, secure_url, qr_model]
     */
    public function generateToken(string $patientId, string $type = 'static_identity_qr', ?int $expiresInMinutes = null): array
    {
        // Example raw token: qrx_7Kq9Mp42X8d1Tz...
        $rawToken = 'qrx_' . Str::random(32);
        $tokenHash = Hash::make($rawToken);
        
        $expiresAt = $expiresInMinutes ? Carbon::now()->addMinutes($expiresInMinutes) : null;

        $qrModel = HealthIdQrToken::create([
            'patient_id' => $patientId,
            'token_hash' => $tokenHash,
            'token_type' => $type,
            'status' => 'active',
            'expires_at' => $expiresAt
        ]);

        $secureUrl = route('verify.qr', ['token' => $rawToken]);

        return [
            'raw_token' => $rawToken,
            'secure_url' => $secureUrl,
            'model' => $qrModel
        ];
    }

    /**
     * Verify a raw QR token and retrieve the underlying QR token record
     */
    public function verifyToken(string $rawToken): ?HealthIdQrToken
    {
        if (!str_starts_with($rawToken, 'qrx_')) {
            return null;
        }

        // Token search needs to be optimized in production (e.g. splitting a lookup ID and secret)
        // Since we are storing only the hash, we must scan or use a lookup ID prefix in production.
        // For this foundation implementation, we will fetch active tokens and verify.
        // In a real high-scale system, the token format would be: qrx_{lookup_id}_{secret}
        
        // For MVP implementation (not highly scalable, but meets requirements):
        $activeTokens = HealthIdQrToken::where('status', 'active')
            ->where(function($query) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
            })->get();

        foreach ($activeTokens as $token) {
            if (Hash::check($rawToken, $token->token_hash)) {
                // Update last used at
                $token->update(['last_used_at' => now()]);
                return $token;
            }
        }

        return null;
    }

    /**
     * Revoke a specific QR token
     */
    public function revokeToken(HealthIdQrToken $token): void
    {
        $token->update([
            'status' => 'revoked',
            'revoked_at' => now()
        ]);
    }
}
