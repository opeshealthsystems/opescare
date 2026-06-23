<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ConsentRequestResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                     => $this->id,
            'patient_id'             => $this->patient_id,
            'requesting_facility_id' => $this->requesting_facility_id,
            'requesting_user_id'     => $this->requesting_user_id,
            'purpose'                => $this->purpose,
            'requested_scope'        => $this->requested_scope,
            'duration_minutes'       => $this->duration_minutes,
            'status'                 => $this->status,
            'created_at'             => $this->created_at?->toISOString(),
            'updated_at'             => $this->updated_at?->toISOString(),
        ];
    }
}
