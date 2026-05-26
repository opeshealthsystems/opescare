<?php
namespace App\Services\Security;

use RuntimeException;

/**
 * Opt-in field-level encryption service.
 * Driver "local" → AES-256-GCM using APP_KEY (dev/test).
 * Driver "aws"   → AWS KMS envelope encryption (production).
 *
 * This service is opt-in only. No existing model or migration is touched.
 */
class KmsEncryptionService
{
    private string $driver;

    public function __construct()
    {
        $this->driver = config('kms.driver', 'local');
    }

    public function encrypt(string $plaintext): string
    {
        return match ($this->driver) {
            'aws'   => $this->encryptAws($plaintext),
            default => $this->encryptLocal($plaintext),
        };
    }

    public function decrypt(string $ciphertext): string
    {
        return match ($this->driver) {
            'aws'   => $this->decryptAws($ciphertext),
            default => $this->decryptLocal($ciphertext),
        };
    }

    public function reEncrypt(string $ciphertext): string
    {
        return $this->encrypt($this->decrypt($ciphertext));
    }

    // ── Local Driver (AES-256-GCM) ──────────────────────────────────────────

    private function encryptLocal(string $plaintext): string
    {
        $key    = $this->localKey();
        $iv     = random_bytes(12);
        $tag    = '';

        $cipher = openssl_encrypt(
            $plaintext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            '',
            16
        );

        if ($cipher === false) {
            throw new RuntimeException('Local encryption failed: ' . openssl_error_string());
        }

        return base64_encode($iv . $tag . $cipher);
    }

    private function decryptLocal(string $ciphertext): string
    {
        $raw = base64_decode($ciphertext, strict: true);

        if ($raw === false || strlen($raw) < 28) {
            throw new RuntimeException('KmsEncryptionService: invalid ciphertext format.');
        }

        $key   = $this->localKey();
        $iv    = substr($raw, 0, 12);
        $tag   = substr($raw, 12, 16);
        $data  = substr($raw, 28);

        $plain = openssl_decrypt(
            $data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag
        );

        if ($plain === false) {
            throw new RuntimeException('KmsEncryptionService: decryption failed — wrong key or tampered data.');
        }

        return $plain;
    }

    private function localKey(): string
    {
        $appKey = config('app.key');
        $raw    = base64_decode(str_replace('base64:', '', $appKey));
        return hash('sha256', $raw, true);
    }

    // ── AWS Driver ────────────────────────────────────────────────────────────

    private function encryptAws(string $plaintext): string
    {
        $kms        = $this->awsClient();
        $result     = $kms->generateDataKey(['KeyId' => config('kms.aws.key_id'), 'KeySpec' => 'AES_256']);
        $dataKey    = (string) $result['Plaintext'];
        $encDataKey = (string) $result['CiphertextBlob'];

        $iv  = random_bytes(12);
        $tag = '';

        $cipher = openssl_encrypt($plaintext, 'aes-256-gcm', $dataKey, OPENSSL_RAW_DATA, $iv, $tag, '', 16);
        sodium_memzero($dataKey);

        $encKeyLen = strlen($encDataKey);
        $packed    = pack('N', $encKeyLen) . $encDataKey . $iv . $tag . $cipher;

        return base64_encode($packed);
    }

    private function decryptAws(string $ciphertext): string
    {
        $raw = base64_decode($ciphertext, strict: true);
        if ($raw === false || strlen($raw) < 32) {
            throw new RuntimeException('KmsEncryptionService: invalid AWS ciphertext format.');
        }

        $encKeyLen  = unpack('N', substr($raw, 0, 4))[1];
        $encDataKey = substr($raw, 4, $encKeyLen);
        $iv         = substr($raw, 4 + $encKeyLen, 12);
        $tag        = substr($raw, 4 + $encKeyLen + 12, 16);
        $data       = substr($raw, 4 + $encKeyLen + 28);

        $kms     = $this->awsClient();
        $result  = $kms->decrypt(['CiphertextBlob' => $encDataKey]);
        $dataKey = (string) $result['Plaintext'];

        $plain = openssl_decrypt($data, 'aes-256-gcm', $dataKey, OPENSSL_RAW_DATA, $iv, $tag);
        sodium_memzero($dataKey);

        if ($plain === false) {
            throw new RuntimeException('KmsEncryptionService: AWS decryption failed.');
        }

        return $plain;
    }

    private function awsClient(): \Aws\Kms\KmsClient
    {
        if (!class_exists(\Aws\Kms\KmsClient::class)) {
            throw new RuntimeException('AWS SDK not installed. Run: composer require aws/aws-sdk-php');
        }
        return new \Aws\Kms\KmsClient([
            'version' => config('kms.aws.version', 'latest'),
            'region'  => config('kms.aws.region', 'eu-west-1'),
        ]);
    }
}
