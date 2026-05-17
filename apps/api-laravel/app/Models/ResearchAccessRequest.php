<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResearchAccessRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'requesting_organization',
        'principal_investigator',
        'purpose',
        'ethics_document_id',
        'requested_dataset_scope_json',
        'status',
        'reviewed_by',
        'approved_at',
        'expires_at',
    ];

    protected $casts = [
        'requested_dataset_scope_json' => 'array',
        'approved_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
