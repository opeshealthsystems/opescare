<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasUuids;

    protected $table = 'academy_certificates';

    protected $fillable = [
        'certificate_number',
        'verification_code',
        'user_id',
        'course_id',
        'level',
        'status',
        'score',
        'issued_at',
        'expires_at',
        'revoked_at',
        'revocation_reason',
        'certificate_pdf_path',
        'certificate_hash',
        'is_demo',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'is_demo' => 'boolean',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tokens()
    {
        return $this->hasMany(CertificateVerificationToken::class, 'certificate_id');
    }

    public function verificationEvents()
    {
        return $this->hasMany(CertificateVerificationEvent::class, 'certificate_id');
    }
}
