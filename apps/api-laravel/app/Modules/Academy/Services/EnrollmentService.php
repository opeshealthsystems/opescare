<?php

namespace App\Modules\Academy\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;

class EnrollmentService
{
    /**
     * Enroll a user in a published course.
     */
    public function enrollUser(string $userId, string $courseId): CourseEnrollment
    {
        $course = Course::findOrFail($courseId);
        if ($course->status !== 'published') {
            throw new \InvalidArgumentException('COURSE_NOT_PUBLISHED');
        }

        // Check prerequisites
        if ($course->prerequisites_json) {
            foreach ($course->prerequisites_json as $prereqCode) {
                $prereqCourse = Course::where('course_code', $prereqCode)->first();
                if ($prereqCourse) {
                    $completed = CourseEnrollment::where('user_id', $userId)
                        ->where('course_id', $prereqCourse->id)
                        ->where('status', 'completed')
                        ->exists();

                    if (!$completed) {
                        throw new \LogicException('COURSE_PREREQUISITE_REQUIRED');
                    }
                }
            }
        }

        return CourseEnrollment::firstOrCreate(
            ['user_id' => $userId, 'course_id' => $courseId],
            [
                'status' => 'enrolled',
                'progress_percentage' => 0,
                'started_at' => now(),
                'is_demo' => $course->is_demo
            ]
        );
    }

    /**
     * Complete a lesson and update overall progress percentage.
     */
    public function completeLesson(string $userId, string $lessonId): LessonProgress
    {
        $lesson = Lesson::findOrFail($lessonId);
        $module = $lesson->module;
        $course = $module->course;

        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            [
                'status' => 'completed',
                'completed_at' => now()
            ]
        );

        // Calculate progress percentage
        $enrollment = CourseEnrollment::where('user_id', $userId)
            ->where('course_id', $course->id)
            ->first();

        if ($enrollment) {
            $totalLessons = Lesson::whereIn('course_module_id', $course->modules->pluck('id'))->count();
            if ($totalLessons > 0) {
                $completedCount = LessonProgress::where('user_id', $userId)
                    ->whereIn('lesson_id', Lesson::whereIn('course_module_id', $course->modules->pluck('id'))->pluck('id'))
                    ->count();

                $percent = (int) (($completedCount / $totalLessons) * 100);
                $enrollment->update([
                    'progress_percentage' => min($percent, 100)
                ]);
            }
        }

        return $progress;
    }

    /**
     * Check if enrollment renewal alerts are needed.
     */
    public function checkRenewals(): array
    {
        $expiring = CourseEnrollment::where('status', 'completed')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(60))
            ->get();

        $alerts = [];
        foreach ($expiring as $en) {
            $daysLeft = now()->diffInDays($en->expires_at, false);
            $alerts[] = [
                'user_id' => $en->user_id,
                'course_id' => $en->course_id,
                'expires_at' => $en->expires_at,
                'days_left' => $daysLeft
            ];
        }

        return $alerts;
    }
}
