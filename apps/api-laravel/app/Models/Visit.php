<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasFactory, HasUuids;

    protected $fillable = [
        'patient_id',
        'facility_id',
        'provider_id',
        'visit_type',
        'status',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function provider()
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function triageRecords()
    {
        return $this->hasMany(TriageRecord::class);
    }

    public function clinicalNotes()
    {
        return $this->hasMany(ClinicalNote::class);
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnosis::class);
    }
}
