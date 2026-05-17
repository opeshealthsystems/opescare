<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WebhookDeliveryLog extends Model
{
    use HasUuids;

    protected $fillable = [
        'event_id',
        'event_type',
        'payload',
        'status',
        'retry_count'
    ];

    protected $casts = [
        'payload' => 'array'
    ];
}
