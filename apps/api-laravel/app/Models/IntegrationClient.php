<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class IntegrationClient extends Model
{
    use HasUuids;

    protected $fillable = [
        'client_id',
        'client_secret',
        'facility_id',
        'scopes',
        'status',
        'environment'
    ];

    protected $casts = [
        'scopes' => 'array'
    ];
}
