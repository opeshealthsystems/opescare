<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SimulationAttempt extends Model
{
    use HasUuids;

    protected $table = 'academy_simulation_attempts';

    protected $fillable = [
        'user_id',
        'course_id',
        'simulation_type',
        'score',
        'status',
        'mistakes_json',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'mistakes_json' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
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
