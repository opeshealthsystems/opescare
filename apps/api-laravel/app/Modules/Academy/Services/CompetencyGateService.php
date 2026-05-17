<?php

namespace App\Modules\Academy\Services;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CompetencyRequirement;

class CompetencyGateService
{
    /**
     * Enforce standard safety barriers: check if a user is authorized to perform a critical digital health action.
     */
    public function authorizeAction(string $userId, string $roleName, ?string $permissionName = null): bool
    {
        // 1. Students can NEVER perform definitive prescriptions or emergency overrides, regardless of certificates
        if ($roleName === 'student' && in_array($permissionName, ['write_prescription', 'emergency_override', 'validate_lab_result'])) {
            return false;
        }

        // 2. Fetch required competency tracks for this role or specific action
        $query = CompetencyRequirement::where('required', true)
            ->where(function($q) use ($roleName, $permissionName) {
                $q->where('role_name', $roleName);
                if ($permissionName) {
                    $q->orWhere('permission_name', $permissionName);
                }
            });

        $requirements = $query->get();

        foreach ($requirements as $req) {
            // Verify if the user possesses an active certificate for this course
            $hasActiveCert = Certificate::where('user_id', $userId)
                ->where('course_id', $req->course_id)
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })
                ->exists();

            if (!$hasActiveCert) {
                return false; // Competency requirement missing or expired
            }
        }

        return true;
    }

    /**
     * Map a role or permission competency constraint.
     */
    public function registerRequirement(string $roleName, string $courseCode, ?string $permissionName = null): CompetencyRequirement
    {
        $course = Course::where('course_code', $courseCode)->firstOrFail();

        return CompetencyRequirement::create([
            'role_name' => $roleName,
            'permission_name' => $permissionName,
            'course_id' => $course->id,
            'required' => true,
            'effective_from' => now()
        ]);
    }
}
