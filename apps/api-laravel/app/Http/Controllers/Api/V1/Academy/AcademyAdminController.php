<?php

namespace App\Http\Controllers\Api\V1\Academy;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CompetencyRequirement;
use App\Models\TrainerSignoff;
use App\Modules\Academy\Services\AcademyReportingService;
use App\Modules\Academy\Services\CertificateService;
use App\Modules\Academy\Services\CourseService;
use App\Modules\Academy\Services\CompetencyGateService;
use Illuminate\Http\Request;

class AcademyAdminController extends Controller
{
    protected $courseService;
    protected $gateService;
    protected $reportingService;
    protected $certificateService;

    public function __construct(
        CourseService $courseService,
        CompetencyGateService $gateService,
        AcademyReportingService $reportingService,
        CertificateService $certificateService
    ) {
        $this->courseService = $courseService;
        $this->gateService = $gateService;
        $this->reportingService = $reportingService;
        $this->certificateService = $certificateService;
    }

    /**
     * Seed core tracks.
     */
    public function seedTracks()
    {
        $this->courseService->seedCoreCourses();
        return response()->json(['message' => 'CORE_COURSES_SEEDED_SUCCESSFULLY']);
    }

    /**
     * Submit trainer/supervisor signoff for a learner simulation challenge.
     */
    public function approveTrainerSignoff(Request $request)
    {
        $learnerId = $request->input('learner_id');
        $courseId = $request->input('course_id');
        $trainerId = $request->input('trainer_id');
        $notes = $request->input('notes', '');

        if (!$learnerId || !$courseId || !$trainerId) {
            return response()->json(['error' => 'MISSING_REQUIRED_PARAMETERS'], 400);
        }

        $signoff = TrainerSignoff::create([
            'learner_id' => $learnerId,
            'course_id' => $courseId,
            'trainer_id' => $trainerId,
            'status' => 'approved',
            'notes' => $notes,
            'signed_at' => now()
        ]);

        // Check if other requirements are satisfied to issue the certificate
        $course = Course::findOrFail($courseId);
        $hasPassedQuiz = \App\Models\QuizAttempt::where('user_id', $learnerId)
            ->whereIn('quiz_id', $course->quizzes->pluck('id'))
            ->where('status', 'passed')
            ->exists();

        $hasPassedSimulation = true;
        if ($course->requires_simulation) {
            $hasPassedSimulation = \App\Models\SimulationAttempt::where('user_id', $learnerId)
                ->where('course_id', $courseId)
                ->where('status', 'passed')
                ->exists();
        }

        if ($hasPassedQuiz && $hasPassedSimulation) {
            $this->certificateService->issueCertificate($learnerId, $courseId, 85);
        }

        return response()->json($signoff, 201);
    }

    /**
     * Register a competency gate mapping a system permission or role to a required course.
     */
    public function registerGate(Request $request)
    {
        $roleName = $request->input('role_name');
        $courseCode = $request->input('course_code');
        $permissionName = $request->input('permission_name');

        if (!$roleName || !$courseCode) {
            return response()->json(['error' => 'ROLE_NAME_AND_COURSE_CODE_REQUIRED'], 400);
        }

        try {
            $gate = $this->gateService->registerRequirement($roleName, $courseCode, $permissionName);
            return response()->json($gate, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get readiness dashboard for facility.
     */
    public function getFacilityReadiness(string $facilityId)
    {
        $report = $this->reportingService->getFacilityReadinessReport($facilityId);
        return response()->json($report);
    }

    /**
     * Revoke certificate.
     */
    public function revokeCertificate(Request $request, string $id)
    {
        $reason = $request->input('reason', 'Administrative revocation');
        try {
            $cert = $this->certificateService->revokeCertificate($id, $reason);
            return response()->json($cert);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Renew certificate.
     */
    public function renewCertificate(string $id)
    {
        try {
            $cert = $this->certificateService->renewCertificate($id);
            return response()->json($cert);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Render administrative facility readiness cockpit dashboard.
     */
    public function readinessDashboard(string $facilityId)
    {
        $result = $this->reportingService->getFacilityReadinessReport($facilityId);
        return view('academy.facility_readiness', compact('result', 'facilityId'));
    }
}
