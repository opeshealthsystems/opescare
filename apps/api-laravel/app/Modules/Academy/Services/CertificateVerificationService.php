<?php

namespace App\Modules\Academy\Services;

use App\Models\Certificate;
use App\Models\CertificateVerificationEvent;
use App\Models\CertificateVerificationToken;
use Illuminate\Http\Request;

class CertificateVerificationService
{
    /**
     * Verify a certificate using a public secure SHA-256 token.
     */
    public function verifyByToken(string $rawToken, Request $request): array
    {
        $hash = hash('sha256', $rawToken);
        $token = CertificateVerificationToken::where('token_hash', $hash)
            ->where('status', 'active')
            ->first();

        $ip = $request->ip() ?? '127.0.0.1';
        $ua = $request->userAgent() ?? 'Unknown';

        if (!$token) {
            $this->logEvent(null, null, $hash, 'failed_not_found', $ip, $ua);
            throw new \InvalidArgumentException('INVALID_OR_EXPIRED_VERIFICATION_TOKEN');
        }

        $certificate = $token->certificate;

        if (!$certificate) {
            $this->logEvent(null, null, $hash, 'failed_not_found', $ip, $ua);
            throw new \InvalidArgumentException('CERTIFICATE_NOT_FOUND');
        }

        // Check if expired
        if ($certificate->expires_at && $certificate->expires_at->isPast()) {
            $certificate->update(['status' => 'expired']);
            $this->logEvent($certificate->id, null, $hash, 'failed_expired', $ip, $ua);
            return $this->formatPublicResult($certificate, 'expired');
        }

        // Check if revoked
        if ($certificate->status === 'revoked') {
            $this->logEvent($certificate->id, null, $hash, 'failed_revoked', $ip, $ua);
            return $this->formatPublicResult($certificate, 'revoked');
        }

        // Update token usage
        $token->update(['last_used_at' => now()]);

        $this->logEvent($certificate->id, null, $hash, 'success', $ip, $ua);

        return $this->formatPublicResult($certificate, 'active');
    }

    /**
     * Verify certificate using verification_code or certificate_number.
     */
    public function verifyByCode(string $code, Request $request): array
    {
        $certificate = Certificate::where('verification_code', $code)
            ->orWhere('certificate_number', $code)
            ->first();

        $ip = $request->ip() ?? '127.0.0.1';
        $ua = $request->userAgent() ?? 'Unknown';

        if (!$certificate) {
            $this->logEvent(null, $code, null, 'failed_not_found', $ip, $ua);
            throw new \InvalidArgumentException('CERTIFICATE_NOT_FOUND');
        }

        // Check if expired
        if ($certificate->expires_at && $certificate->expires_at->isPast()) {
            $certificate->update(['status' => 'expired']);
            $this->logEvent($certificate->id, $code, null, 'failed_expired', $ip, $ua);
            return $this->formatPublicResult($certificate, 'expired');
        }

        // Check if revoked
        if ($certificate->status === 'revoked') {
            $this->logEvent($certificate->id, $code, null, 'failed_revoked', $ip, $ua);
            return $this->formatPublicResult($certificate, 'revoked');
        }

        $this->logEvent($certificate->id, $code, null, 'success', $ip, $ua);

        return $this->formatPublicResult($certificate, 'active');
    }

    /**
     * Formats public response to filter personal HR metrics while proving validity.
     */
    protected function formatPublicResult(Certificate $cert, string $resultState): array
    {
        return [
            'certificate_number' => $cert->certificate_number,
            'status' => $cert->status,
            'result' => $resultState,
            'course' => [
                'course_code' => $cert->course->course_code,
                'title_en' => $cert->course->title_en,
                'title_fr' => $cert->course->title_fr,
                'level' => $cert->course->level
            ],
            'learner' => [
                // Mask the user's real name for public privacy protection
                'name_masked' => $this->maskName($cert->user->name),
                'role' => $cert->user->role_id ?? 'staff'
            ],
            'issued_at' => $cert->issued_at->toIso8601String(),
            'expires_at' => $cert->expires_at ? $cert->expires_at->toIso8601String() : null,
            'revoked_at' => $cert->revoked_at ? $cert->revoked_at->toIso8601String() : null,
            'revocation_reason' => $cert->revoked_at ? $cert->revocation_reason : null,
        ];
    }

    /**
     * Mask user name for secure GDPR compliance.
     */
    protected function maskName(string $name): string
    {
        $parts = explode(' ', $name);
        $maskedParts = [];
        foreach ($parts as $part) {
            if (strlen($part) <= 2) {
                $maskedParts[] = $part;
            } else {
                $maskedParts[] = substr($part, 0, 1) . str_repeat('*', strlen($part) - 2) . substr($part, -1);
            }
        }
        return implode(' ', $maskedParts);
    }

    /**
     * Log verification events to the immutable audit database table.
     */
    protected function logEvent(
        ?string $certId,
        ?string $code,
        ?string $tokenHash,
        string $result,
        string $ip,
        string $ua
    ): void {
        CertificateVerificationEvent::create([
            'certificate_id' => $certId,
            'verification_code' => $code,
            'token_hash' => $tokenHash,
            'result' => $result,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'public_verification' => true
        ]);
    }
}
