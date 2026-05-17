<?php

namespace App\Modules\Academy\Services;

use App\Models\Certificate;
use App\Models\CertificateVerificationToken;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Support\Str;

class CertificateService
{
    /**
     * Issue a new certificate for a completed track.
     */
    public function issueCertificate(string $userId, string $courseId, ?int $score = null): Certificate
    {
        $course = Course::findOrFail($courseId);

        // Modulo-36 Check Digit Serial Number Generator
        $country = 'XX'; // default or look up from user profile if available
        $track = substr($course->course_code, -4); // e.g. 101 or CLIN
        $year = date('Y');
        $random = strtoupper(Str::random(6));

        $base = "CERT-{$country}-{$track}-{$year}-{$random}";
        
        // Modulo-36 check character
        $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $sum = 0;
        for ($i = 0; $i < strlen($base); $i++) {
            $sum += ord($base[$i]);
        }
        $checkChar = $charset[$sum % 36];
        $serial = "{$base}-{$checkChar}";

        $verificationCode = strtoupper(Str::random(12));

        // Calculate expiry date
        $expiresAt = now()->addMonths($course->validity_months);

        $certificate = Certificate::create([
            'certificate_number' => $serial,
            'verification_code' => $verificationCode,
            'user_id' => $userId,
            'course_id' => $courseId,
            'level' => $course->level,
            'status' => 'active',
            'score' => $score,
            'issued_at' => now(),
            'expires_at' => $expiresAt,
            'is_demo' => $course->is_demo
        ]);

        // Automatically update enrollment status to completed
        CourseEnrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'expires_at' => $expiresAt
            ]);

        // Pre-generate a public share/verification token hash
        $this->generateVerificationToken($certificate->id);

        return $certificate;
    }

    /**
     * Generate secure SHA-256 verification token.
     */
    public function generateVerificationToken(string $certificateId): CertificateVerificationToken
    {
        $rawToken = Str::random(40);
        $hash = hash('sha256', $rawToken);

        return CertificateVerificationToken::create([
            'certificate_id' => $certificateId,
            'token_hash' => $hash,
            'status' => 'active',
            'expires_at' => now()->addYears(2)
        ]);
    }

    /**
     * Revoke an active certificate.
     */
    public function revokeCertificate(string $id, string $reason): Certificate
    {
        $certificate = Certificate::findOrFail($id);
        $certificate->update([
            'status' => 'revoked',
            'revoked_at' => now(),
            'revocation_reason' => $reason
        ]);

        // Revoke associated active share tokens
        CertificateVerificationToken::where('certificate_id', $id)
            ->update([
                'status' => 'revoked',
                'revoked_at' => now()
            ]);

        return $certificate;
    }

    /**
     * Renew an expired or expiring certificate.
     */
    public function renewCertificate(string $id): Certificate
    {
        $certificate = Certificate::findOrFail($id);
        $course = $certificate->course;

        $expiresAt = now()->addMonths($course->validity_months);

        $certificate->update([
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => $expiresAt,
            'revoked_at' => null,
            'revocation_reason' => null
        ]);

        // Mark enrollment as completed again
        CourseEnrollment::where('user_id', $certificate->user_id)
            ->where('course_id', $certificate->course_id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
                'expires_at' => $expiresAt
            ]);

        return $certificate;
    }
}
