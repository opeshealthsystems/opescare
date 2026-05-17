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
            $matches = Patient::where('first_name', $data['first_name'])
                ->where('last_name', $data['last_name'])
                ->where('phone_number', $data['phone_number'])
                ->whereDate('date_of_birth', $formattedDob)
                ->where('sex', $data['sex'])
                ->get();
            $candidates = $candidates->merge($matches);
        }

        return $candidates->unique('id');
    }
}
