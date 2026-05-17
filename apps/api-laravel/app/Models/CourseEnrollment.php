<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use \App\Traits\IsDemoRecord;
    use HasUuids;

    protected $table = 'academy_course_enrollments';

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'progress_percentage',
        'started_at',
        'completed_at',
        'expires_at',
        'is_demo',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
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
}
