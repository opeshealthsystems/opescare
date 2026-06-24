<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

/**
 * Wire contract for an App\Models\ResearchAccessRequest. Add fields here deliberately;
 * never expose the model directly.
 */
class ResearchAccessRequestResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                           => $this->id,
            'requesting_organization'      => $this->requesting_organization,
            'principal_investigator'       => $this->principal_investigator,
            'purpose'                      => $this->purpose,
            'ethics_document_id'           => $this->ethics_document_id,
            'requested_dataset_scope_json' => $this->requested_dataset_scope_json,
            'status'                       => $this->status,
            'reviewed_by'                  => $this->reviewed_by,
            'approved_at'                  => $this->approved_at?->toISOString(),
            'expires_at'                   => $this->expires_at?->toISOString(),
            'created_at'                   => $this->created_at?->toISOString(),
            'updated_at'                   => $this->updated_at?->toISOString(),
        ];
    }
}
