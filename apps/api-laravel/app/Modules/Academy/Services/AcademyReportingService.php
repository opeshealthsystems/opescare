<?php

namespace App\Modules\Academy\Services;

use App\Models\Certificate;
use App\Models\CourseEnrollment;
use App\Models\User;

class AcademyReportingService
{
    /**
     * Compile digital health readiness percentage for a facility.
     */
    public function getFacilityReadinessReport(string $facilityId): array
    {
        // Fetch all active staff users at the facility
        $users = User::where('primary_facility_id', $facilityId)->get();
        $totalUsers = $users->count();

        if ($totalUsers === 0) {
            return [
                'readiness_percentage' => 100,
                'total_staff' => 0,
                'fully_certified_staff' => 0,
                'active_learners' => 0
            ];
        }

        $certifiedCount = 0;
        $activeLearners = 0;

        foreach ($users as $user) {
            // Check if user has completed OPC-FOUND-101 and OPC-PRIV-101 (core safety certificates)
            $completedCore = Certificate::where('user_id', $user->id)
                ->whereIn('course_id', function($q) {
                    $q->select('id')
                      ->from('academy_courses')
                      ->whereIn('course_code', ['OPC-FOUND-101', 'OPC-PRIV-101']);
                })
                ->where('status', 'active')
                ->count();

            if ($completedCore >= 2) {
                $certifiedCount++;
            }

            // Check if enrolled in other courses
            $enrolled = CourseEnrollment::where('user_id', $user->id)
                ->where('status', 'enrolled')
                ->exists();

            if ($enrolled) {
                $activeLearners++;
            }
        }

        $readiness = (int) (($certifiedCount / $totalUsers) * 100);

        return [
            'readiness_percentage' => $readiness,
            'total_staff' => $totalUsers,
            'fully_certified_staff' => $certifiedCount,
            'active_learners' => $activeLearners
        ];
    }
}
