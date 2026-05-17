<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasUuids;

    protected $table = 'academy_lessons';

    protected $fillable = [
        'course_module_id',
        'lesson_type',
        'title_en',
        'title_fr',
        'content_en',
        'content_fr',
        'video_url',
        'resource_url',
        'sort_order',
        'estimated_minutes',
        'status',
    ];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'course_module_id');
    }

    public function progressLogs()
    {
        return $this->hasMany(LessonProgress::class, 'lesson_id');
    }
}
