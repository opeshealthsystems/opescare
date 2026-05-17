<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CertificateVerificationEvent extends Model
{
    use HasUuids;

    protected $table = 'academy_certificate_verification_events';

    public $timestamps = false; // Trigger events log is immutable and writes raw created_at

    protected $fillable = [
        'certificate_id',
        'verification_code',
        'token_hash',
        'result',
        'ip_address',
        'user_agent',
        'verified_by_user_id',
        'public_verification',
        'created_at',
    ];

    protected $casts = [
        'public_verification' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function certificate()
    {
        return $this->belongsTo(Certificate::class, 'certificate_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }
}
