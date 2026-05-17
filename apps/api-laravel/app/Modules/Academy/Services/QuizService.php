<?php

namespace App\Modules\Academy\Services;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;

class QuizService
{
    /**
     * Start a quiz attempt.
     */
    public function startQuiz(string $userId, string $quizId): QuizAttempt
    {
        $quiz = Quiz::findOrFail($quizId);
        if ($quiz->status !== 'active') {
            throw new \InvalidArgumentException('QUIZ_NOT_FOUND');
        }

        // Limit attempts if max_attempts is configured
        if ($quiz->max_attempts) {
            $pastAttempts = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $quizId)
                ->count();

            if ($pastAttempts >= $quiz->max_attempts) {
                // If the user already passed, allow it or throw limit
                $hasPassed = QuizAttempt::where('user_id', $userId)
                    ->where('quiz_id', $quizId)
                    ->where('status', 'passed')
                    ->exists();

                if (!$hasPassed) {
                    throw new \LogicException('QUIZ_ATTEMPT_LIMIT_REACHED');
                }
            }
        }

        $attemptNumber = QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->count() + 1;

        return QuizAttempt::create([
            'user_id' => $userId,
            'quiz_id' => $quizId,
            'attempt_number' => $attemptNumber,
            'score' => 0,
            'status' => 'failed',
            'started_at' => now(),
            'submitted_at' => null
        ]);
    }

    /**
     * Submit answers and score the attempt.
     */
    public function submitQuiz(string $attemptId, array $answers): QuizAttempt
    {
        $attempt = QuizAttempt::findOrFail($attemptId);
        if ($attempt->submitted_at) {
            throw new \LogicException('QUIZ_TIME_EXPIRED');
        }

        $quiz = $attempt->quiz;
        $questions = $quiz->questions;
        $totalPoints = 0;
        $earnedPoints = 0;

        foreach ($questions as $q) {
            $totalPoints += $q->points;
            $userAns = $answers[$q->id] ?? null;

            // Correct answer validation
            $correct = $q->correct_answer_json;
            if (is_array($userAns) && is_array($correct)) {
                // Sort arrays and match
                sort($userAns);
                sort($correct);
                if ($userAns === $correct) {
                    $earnedPoints += $q->points;
                }
            } else if ($userAns == $correct) {
                $earnedPoints += $q->points;
            }
        }

        $score = $totalPoints > 0 ? (int) (($earnedPoints / $totalPoints) * 100) : 0;
        $status = $score >= $quiz->passing_score ? 'passed' : 'failed';

        $attempt->update([
            'score' => $score,
            'status' => $status,
            'submitted_at' => now()
        ]);

        return $attempt;
    }
}
