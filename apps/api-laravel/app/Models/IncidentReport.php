<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentReport extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'support_ticket_id',
        'security_incident_id',
        'severity',
        'summary',
        'created_by',
    ];
}
