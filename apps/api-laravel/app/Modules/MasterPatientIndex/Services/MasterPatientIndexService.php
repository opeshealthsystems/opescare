<?php

namespace App\Modules\MasterPatientIndex\Services;

use App\Models\Patient;
use App\Models\PatientIdentifier;

class MasterPatientIndexService
{
    public function searchCandidates(array $data)
    {
        $candidates = collect();

        if (!empty($data['identifiers'])) {
            foreach ($data['identifiers'] as $identifier) {
                $matches = PatientIdentifier::where('identifier_type', $identifier['type'])
                    ->where('identifier_value', $identifier['value'])
                    ->with('patient')
                    ->get()
                    ->pluck('patient')
                    ->filter();

                $candidates = $candidates->merge($matches);
            }
        }

        if (!empty($data['first_name']) && !empty($data['last_name']) && !empty($data['phone_number']) && !empty($data['date_of_birth']) && !empty($data['sex'])) {
            $formattedDob = \Carbon\Carbon::parse($data['date_of_birth'])->format('Y-m-d');
            // phone_number is encrypted — use phone_number_hash for the DB query,
            // then verify date_of_birth in PHP (also encrypted, not queryable).
            $matches = Patient::where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->where('phone_number_hash', Patient::phoneHash($data['phone_number']))
                ->where('sex', $data['sex'])
                ->get()
                ->filter(fn ($p) => $p->date_of_birth?->format('Y-m-d') === $formattedDob);
            $candidates = $candidates->merge($matches);
        }

        return $candidates->unique('id');
    }
}
