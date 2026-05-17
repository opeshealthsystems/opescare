<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    use HasUuids;

    protected $table = 'academy_course_modules';

    protected $fillable = [
        'course_id',
        'title_en',
        'title_fr',
        'description_en',
        'description_fr',
        'sort_order',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_module_id')->orderBy('sort_order');
    }
}
