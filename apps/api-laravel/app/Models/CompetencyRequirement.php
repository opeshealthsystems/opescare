<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompetencyRequirement extends Model
{
    use HasUuids;

    protected $table = 'academy_competency_requirements';

    protected $fillable = [
        'role_name',
        'permission_name',
        'course_id',
        'required',
        'effective_from',
        'expires_at',
    ];

    protected $casts = [
        'required' => 'boolean',
        'effective_from' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
