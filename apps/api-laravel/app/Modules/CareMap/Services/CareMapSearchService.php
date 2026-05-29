<?php

namespace App\Modules\CareMap\Services;

use App\Models\CareFacility;
use Illuminate\Support\Facades\DB;

class CareMapSearchService
{
    /**
     * Search for care facilities based on latitude, longitude, distance, type, and status rules.
     */
    public function searchNearby(array $params)
    {
        $lat       = $params['latitude']      ?? null;
        $lon       = $params['longitude']     ?? null;
        $radius    = $params['radius']        ?? 50;   // km
        $type      = $params['facility_type'] ?? null;
        $insurance = $params['insurance_name'] ?? null;
        $emergency = $params['emergency']     ?? false;
        $queryText = trim($params['query']    ?? '');
        $city      = trim($params['city']     ?? '');

        $builder = CareFacility::query();

        // 1. Only active listings
        $builder->where('listing_status', 'active');

        // 2. Unverified filter
        if (!($params['include_unverified'] ?? true)) {
            $builder->where('verification_status', '!=', 'unverified');
        }

        // 3. Facility type
        if ($type) {
            $builder->where('facility_type', $type);
        }

        // 4. Emergency (hospitals with an emergency contact)
        if ($emergency) {
            $builder->where('facility_type', 'hospital')
                    ->where('emergency_contact', '!=', '');
        }

        // 5. Insurance network
        if ($insurance) {
            $builder->whereHas('insurances', function ($q) use ($insurance) {
                $q->where('insurance_name', 'like', "%{$insurance}%")
                  ->where('status', 'active');
            });
        }

        // 6. Full-text query across name / type / city / description
        if ($queryText !== '') {
            $builder->where(function ($q) use ($queryText) {
                $q->where('facility_name', 'like', "%{$queryText}%")
                  ->orWhere('facility_type', 'like', "%{$queryText}%")
                  ->orWhere('city',          'like', "%{$queryText}%")
                  ->orWhere('description',   'like', "%{$queryText}%");
            });
        }

        // 7. City filter
        if ($city !== '') {
            $builder->where('city', 'like', "%{$city}%");
        }

        // 8. Geospatial: Haversine distance filter + ordering
        if ($lat !== null && $lon !== null) {
            $builder->whereNotNull('latitude')->whereNotNull('longitude');

            if (DB::getDriverName() === 'sqlite') {
                // SQLite has no trig functions — calculate distance in PHP
                $results  = $builder->with(['services', 'hours', 'insurances'])->get();
                $filtered = [];
                foreach ($results as $facility) {
                    $d = $this->calculatePhpDistance($lat, $lon, $facility->latitude, $facility->longitude);
                    if ($d <= $radius) {
                        $facility->distance = round($d, 1);
                        $filtered[] = $facility;
                    }
                }
                usort($filtered, fn ($a, $b) => $a->distance <=> $b->distance);
                return collect($filtered);
            }

            // PostgreSQL / MySQL — use whereRaw so the distance expression is in the
            // WHERE clause, not HAVING. PostgreSQL disallows column aliases in HAVING
            // but allows them in ORDER BY.
            $formula  = "(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))";
            $bindings = [$lat, $lon, $lat];

            $builder->selectRaw("*, {$formula} AS distance", $bindings)
                    ->whereRaw("{$formula} <= ?", [...$bindings, $radius])
                    ->orderBy('distance');
        } else {
            $builder->orderBy('facility_name');
        }

        return $builder->with(['services', 'hours', 'insurances'])->get();
    }

    private function calculatePhpDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }

    /**
     * Emergency specific search - prioritizes 24/7 emergency centers
     */
    public function searchEmergency(array $params)
    {
        $params['emergency'] = true;
        return $this->searchNearby($params);
    }
}

