<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasUuids;

    protected $table = 'academy_quiz_questions';

    protected $fillable = [
        'quiz_id',
        'question_type',
        'question_text_en',
        'question_text_fr',
        'options_json_en',
        'options_json_fr',
        'correct_answer_json',
        'explanation_en',
        'explanation_fr',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'options_json_en' => 'array',
        'options_json_fr' => 'array',
        'correct_answer_json' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id');
    }
}
