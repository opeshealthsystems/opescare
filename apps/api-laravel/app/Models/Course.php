<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasUuids;

    protected $table = 'academy_courses';

    protected $fillable = [
        'course_code',
        'title_en',
        'title_fr',
        'description_en',
        'description_fr',
        'level',
        'target_audience_json',
        'prerequisites_json',
        'status',
        'language',
        'validity_months',
        'passing_score',
        'cpd_credits',
        'requires_simulation',
        'requires_supervisor_signoff',
        'created_by',
        'approved_by',
        'published_at',
        'is_demo',
        'demo_seed_key',
        'demo_reset_group',
    ];

    protected $casts = [
        'target_audience_json' => 'array',
        'prerequisites_json' => 'array',
        'published_at' => 'datetime',
        'requires_simulation' => 'boolean',
        'requires_supervisor_signoff' => 'boolean',
        'is_demo' => 'boolean',
    ];

    public function modules()
    {
        return $this->hasMany(CourseModule::class, 'course_id')->orderBy('sort_order');
    }

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'course_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'course_id');
    }

    public function simulationAttempts()
    {
        return $this->hasMany(SimulationAttempt::class, 'course_id');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class, 'course_id');
    }
}
