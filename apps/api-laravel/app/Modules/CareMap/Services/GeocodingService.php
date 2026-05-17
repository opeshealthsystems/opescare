<?php

namespace App\Modules\CareMap\Services;

class GeocodingService
{
    /**
     * Translate address string into coordinates
     */
    public function geocodeAddress($addressString)
    {
        // For testing/mocking/stable setups, provide regional coordinates fallback
        if (str_contains(strtolower($addressString), 'paris')) {
            return [
                'latitude' => 48.8566,
                'longitude' => 2.3522,
                'accuracy' => 'city_level',
                'source' => 'mock_resolver',
            ];
        }

        if (str_contains(strtolower($addressString), 'london')) {
            return [
                'latitude' => 51.5074,
                'longitude' => -0.1278,
                'accuracy' => 'city_level',
                'source' => 'mock_resolver',
            ];
        }

        // Safe fallback coordinates
        return [
            'latitude' => 38.9072,
            'longitude' => -77.0369,
            'accuracy' => 'area_level',
            'source' => 'mock_resolver',
        ];
    }
}
