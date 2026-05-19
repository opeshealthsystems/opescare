<?php

namespace App\Modules\Fhir\Mappers;

use App\Models\Patient;

/**
 * FHIR R4 Patient Resource Mapper
 *
 * Maps an OpesCare Patient model to a FHIR R4 Patient resource JSON.
 * Reference: https://hl7.org/fhir/R4/patient.html
 */
class FhirPatientMapper
{
    public function toFhir(Patient $patient): array
    {
        $resource = [
            'resourceType' => 'Patient',
            'id'           => $patient->id,
            'meta'         => [
                'lastUpdated' => $patient->updated_at?->toIso8601String(),
                'source'      => 'OpesCare',
            ],
            'identifier' => [
                [
                    'use'    => 'official',
                    'system' => 'https://opescare.com/fhir/health-id',
                    'value'  => $patient->health_id,
                ],
            ],
            'active' => $patient->identity_status !== 'suspended',
            'name'   => [
                [
                    'use'    => 'official',
                    'family' => $patient->last_name,
                    'given'  => array_filter([$patient->first_name, $patient->middle_name ?? null]),
                ],
            ],
        ];

        if ($patient->date_of_birth) {
            $resource['birthDate'] = $patient->date_of_birth->toDateString();
        }

        if ($patient->sex) {
            $resource['gender'] = $this->mapSex($patient->sex);
        }

        if ($patient->phone_number) {
            $resource['telecom'] = [
                [
                    'system' => 'phone',
                    'value'  => $patient->phone_number,
                    'use'    => 'mobile',
                ],
            ];
        }

        if ($patient->address) {
            $address = is_string($patient->address) ? ['text' => $patient->address] : (array) $patient->address;
            $resource['address'] = [
                array_merge(['use' => 'home'], $address),
            ];
        }

        return $resource;
    }

    private function mapSex(string $sex): string
    {
        return match (strtolower($sex)) {
            'male'   => 'male',
            'female' => 'female',
            'other'  => 'other',
            default  => 'unknown',
        };
    }
}
