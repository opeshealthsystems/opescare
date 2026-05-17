<?php

namespace App\Services\Identity;

use Illuminate\Support\Str;
use App\Models\Patient;

class HealthIdGeneratorService
{
    /**
     * Safe alphabet omitting confusing characters (0, O, I, 1, L)
     */
    const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * Generate a new unique Health ID.
     * Format: COUNTRY-HID-BLOCK1-BLOCK2-CHECKBLOCK
     * Example: CM-HID-7KQ9-MP42-X8D1
     * 
     * @param string $countryCode 2-letter ISO country code
     * @param int $maxRetries Number of times to retry on collision
     * @return string
     * @throws \Exception If unique ID cannot be generated
     */
    public function generate(string $countryCode = 'CM', int $maxRetries = 5): string
    {
        $countryCode = strtoupper(substr($countryCode, 0, 2));

        for ($i = 0; $i < $maxRetries; $i++) {
            $block1 = $this->generateRandomBlock(4);
            $block2 = $this->generateRandomBlock(4);
            
            // Check block calculation
            $baseForChecksum = $countryCode . 'HID' . $block1 . $block2;
            $checkBlock = $this->calculateCheckBlock($baseForChecksum);

            $healthId = sprintf('%s-HID-%s-%s-%s', $countryCode, $block1, $block2, $checkBlock);

            if ($this->isUnique($healthId)) {
                return $healthId;
            }
        }

        throw new \Exception("Failed to generate a unique Health ID after {$maxRetries} attempts.");
    }

    /**
     * Generate a secure random block using the safe alphabet
     */
    protected function generateRandomBlock(int $length): string
    {
        $block = '';
        $maxIndex = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $block .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return $block;
    }

    /**
     * Calculate a verifiable check block
     * Using a simple secure modulo approach based on the safe alphabet
     */
    protected function calculateCheckBlock(string $baseString): string
    {
        $hash = hash('sha256', $baseString);
        
        // Take the first 8 hex characters and convert to an integer
        $intVal = hexdec(substr($hash, 0, 8));
        
        $checkBlock = '';
        $base = strlen(self::ALPHABET);
        
        // Generate a 4-character check block
        for ($i = 0; $i < 4; $i++) {
            $remainder = $intVal % $base;
            $checkBlock .= self::ALPHABET[$remainder];
            $intVal = intdiv($intVal, $base);
        }
        
        // Reverse to ensure higher-order bits are leading
        return strrev($checkBlock);
    }

    /**
     * Verify if a Health ID is valid format and checksum
     */
    public function isValid(string $healthId): bool
    {
        // Must match regex: ^[A-Z]{2}-HID-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$
        if (!preg_match('/^[A-Z]{2}-HID-['.self::ALPHABET.']{4}-['.self::ALPHABET.']{4}-['.self::ALPHABET.']{4}$/', $healthId)) {
            return false;
        }

        $parts = explode('-', $healthId);
        $country = $parts[0];
        $block1 = $parts[2];
        $block2 = $parts[3];
        $providedCheckBlock = $parts[4];

        $baseForChecksum = $country . 'HID' . $block1 . $block2;
        $expectedCheckBlock = $this->calculateCheckBlock($baseForChecksum);

        return hash_equals($expectedCheckBlock, $providedCheckBlock);
    }

    /**
     * Check if the generated ID already exists in the database
     */
    protected function isUnique(string $healthId): bool
    {
        return !Patient::where('health_id', $healthId)->exists();
    }
}
