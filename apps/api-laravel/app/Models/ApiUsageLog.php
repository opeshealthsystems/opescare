<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ApiUsageLog extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'integration_client_id',
        'endpoint',
        'method',
        'response_status',
        'response_time_ms',
        'ip_address',
        'facility_id',
        'logged_at',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    /** Append-only — cannot be updated */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new \LogicException('ApiUsageLog is append-only and cannot be updated.');
    }
}
