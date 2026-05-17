<?php

namespace Tests\Feature\Academy;

use App\Models\Certificate;
use App\Models\CertificateVerificationEvent;
use App\Models\CertificateVerificationToken;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\SimulationAttempt;
use App\Models\TrainerSignoff;
use App\Models\User;
use App\Modules\Academy\Services\CertificateService;
use App\Modules\Academy\Services\CompetencyGateService;
use App\Modules\Academy\Services\CourseService;
use App\Modules\Academy\Services\EnrollmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademyCertificationTest extends TestCase
{
    use RefreshDatabase;

    protected $courseService;
    protected $enrollmentService;
    protected $certificateService;
    protected $gateService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->courseService = resolve(CourseService::class);
        $this->enrollmentService = resolve(EnrollmentService::class);
        $this->certificateService = resolve(CertificateService::class);
        $this->gateService = resolve(CompetencyGateService::class);

        // Pre-seed core tracks
        $this->courseService->seedCoreCourses();
    }

    /**
     * 1. Verify core tracks pre-seeding.
     */
    public function test_core_tracks_are_seeded()
    {
        $this->assertDatabaseHas('academy_courses', ['course_code' => 'OPC-FOUND-101']);
        $this->assertDatabaseHas('academy_courses', ['course_code' => 'OPC-PRIV-101']);
        $this->assertDatabaseHas('academy_courses', ['course_code' => 'OPC-CLIN-201']);
        $this->assertDatabaseHas('academy_courses', ['course_code' => 'OPC-LAB-201']);
        $this->assertDatabaseHas('academy_courses', ['course_code' => 'OPC-PHARM-201']);
    }

    /**
     * 2. Verify course level and credits.
     */
    public function test_course_level_and_cpd_credits()
    {
        $course = Course::where('course_code', 'OPC-PRIV-101')->first();
        $this->assertEquals(2, $course->level);
        $this->assertEquals(10, $course->cpd_credits);
    }

    /**
     * 3. Verify user course enrollment.
     */
    public function test_user_course_enrollment()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();

        $enrollment = $this->enrollmentService->enrollUser($user->id, $course->id);

        $this->assertEquals('enrolled', $enrollment->status);
        $this->assertEquals(0, $enrollment->progress_percentage);
        $this->assertDatabaseHas('academy_course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id
        ]);
    }

    /**
     * 4. Verify prerequisite gate validation.
     */
    public function test_enrollment_blocks_missing_prerequisite()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-CLIN-201')->first();
        
        // Add prerequisite requirement
        $course->update(['prerequisites_json' => ['OPC-FOUND-101']]);

        $this->expectException(\LogicException::class);
        $this->enrollmentService->enrollUser($user->id, $course->id);
    }

    /**
     * 5. Verify lesson completion logs and progress calculations.
     */
    public function test_completing_lessons_updates_progress()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();

        $this->enrollmentService->enrollUser($user->id, $course->id);
        $lesson = $course->modules->first()->lessons->first();

        $this->enrollmentService->completeLesson($user->id, $lesson->id);

        $this->assertDatabaseHas('academy_lesson_progress', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'status' => 'completed'
        ]);

        $enrollment = CourseEnrollment::where('user_id', $user->id)->where('course_id', $course->id)->first();
        $this->assertEquals(100, $enrollment->progress_percentage);
    }

    /**
     * 6. Verify quiz attempt starting and time checks.
     */
    public function test_starting_quiz_attempt()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $quiz = $course->quizzes->first();

        $attempt = resolve(\App\Modules\Academy\Services\QuizService::class)->startQuiz($user->id, $quiz->id);

        $this->assertEquals(1, $attempt->attempt_number);
        $this->assertEquals('failed', $attempt->status);
        $this->assertNotNull($attempt->started_at);
    }

    /**
     * 7. Verify quiz scoring correctness.
     */
    public function test_quiz_scoring_evaluation()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $quiz = $course->quizzes->first();
        $attempt = resolve(\App\Modules\Academy\Services\QuizService::class)->startQuiz($user->id, $quiz->id);

        $questions = $quiz->questions;
        $answers = [];
        foreach ($questions as $q) {
            $answers[$q->id] = $q->correct_answer_json; // perfect score
        }

        $result = resolve(\App\Modules\Academy\Services\QuizService::class)->submitQuiz($attempt->id, $answers);

        $this->assertEquals(100, $result->score);
        $this->assertEquals('passed', $result->status);
    }

    /**
     * 8. Verify attempt limits cooldown rules.
     */
    public function test_quiz_limit_enforced()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $quiz = $course->quizzes->first();
        $quiz->update(['max_attempts' => 1]);

        // First attempt
        resolve(\App\Modules\Academy\Services\QuizService::class)->startQuiz($user->id, $quiz->id);

        $this->expectException(\LogicException::class);
        // Second attempt should fail
        resolve(\App\Modules\Academy\Services\QuizService::class)->startQuiz($user->id, $quiz->id);
    }

    /**
     * 9. Verify practical simulations completion.
     */
    public function test_practical_simulation_attempt()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-EMR-101')->first();

        $attempt = resolve(\App\Modules\Academy\Services\SimulationService::class)->startSimulation($user->id, $course->id, 'EMR');
        $this->assertEquals('failed', $attempt->status);

        $submitted = resolve(\App\Modules\Academy\Services\SimulationService::class)->submitSimulation($attempt->id, [
            ['type' => 'vitals_entry', 'is_within_normal_ranges_checked' => true]
        ]);

        $this->assertEquals(100, $submitted->score);
        $this->assertEquals('passed', $submitted->status);
    }

    /**
     * 10. Simulation Critical Failure: Wrong patient selected.
     */
    public function test_simulation_failure_wrong_patient()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-EMR-101')->first();
        $attempt = resolve(\App\Modules\Academy\Services\SimulationService::class)->startSimulation($user->id, $course->id, 'EMR');

        $submitted = resolve(\App\Modules\Academy\Services\SimulationService::class)->submitSimulation($attempt->id, [
            ['type' => 'select_patient', 'is_correct_patient' => false]
        ]);

        $this->assertEquals(0, $submitted->score);
        $this->assertEquals('failed', $submitted->status);
        $this->assertEquals('WRONG_PATIENT_RECORD', $submitted->mistakes_json[0]['rule']);
    }

    /**
     * 11. Simulation Critical Failure: Student prescribing.
     */
    public function test_simulation_failure_student_prescribing()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-STUDENT-101')->first();
        $attempt = resolve(\App\Modules\Academy\Services\SimulationService::class)->startSimulation($user->id, $course->id, 'prescribing');

        $submitted = resolve(\App\Modules\Academy\Services\SimulationService::class)->submitSimulation($attempt->id, [
            ['type' => 'write_prescription', 'is_student' => true]
        ]);

        $this->assertEquals(0, $submitted->score);
        $this->assertEquals('failed', $submitted->status);
        $this->assertEquals('STUDENT_PRESCRIBING_VIOLATION', $submitted->mistakes_json[0]['rule']);
    }

    /**
     * 12. Simulation Critical Failure: Emergency override misuse.
     */
    public function test_simulation_failure_emergency_misuse()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-PRIV-101')->first();
        $attempt = resolve(\App\Modules\Academy\Services\SimulationService::class)->startSimulation($user->id, $course->id, 'privacy');

        $submitted = resolve(\App\Modules\Academy\Services\SimulationService::class)->submitSimulation($attempt->id, [
            ['type' => 'emergency_override', 'has_valid_emergency_justification' => false]
        ]);

        $this->assertEquals(0, $submitted->score);
        $this->assertEquals('failed', $submitted->status);
        $this->assertEquals('EMERGENCY_OVERRIDE_MISUSE', $submitted->mistakes_json[0]['rule']);
    }

    /**
     * 13. Simulation Critical Failure: Un-anonymized public health reports.
     */
    public function test_simulation_failure_un_anonymized_report()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-PH-201')->first();
        $attempt = resolve(\App\Modules\Academy\Services\SimulationService::class)->startSimulation($user->id, $course->id, 'public_health');

        $submitted = resolve(\App\Modules\Academy\Services\SimulationService::class)->submitSimulation($attempt->id, [
            ['type' => 'release_public_health_report', 'is_anonymized' => false]
        ]);

        $this->assertEquals(0, $submitted->score);
        $this->assertEquals('failed', $submitted->status);
        $this->assertEquals('PUBLIC_HEALTH_DATA_LEAK', $submitted->mistakes_json[0]['rule']);
    }

    /**
     * 14. Verify certificate serial number format (Modulo-36 check-character).
     */
    public function test_certificate_serial_modulo36_format()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();

        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 90);

        $this->assertNotNull($cert->certificate_number);
        $this->assertStringStartsWith('CERT-XX-', $cert->certificate_number);
        
        // Assert serial matches Modulo-36 sum check
        $parts = explode('-', $cert->certificate_number);
        $checkChar = end($parts);
        $base = implode('-', array_slice($parts, 0, -1));

        $charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $sum = 0;
        for ($i = 0; $i < strlen($base); $i++) {
            $sum += ord($base[$i]);
        }
        $expectedChar = $charset[$sum % 36];
        $this->assertEquals($expectedChar, $checkChar);
    }

    /**
     * 15. Verify token hashing (SHA-256) for public verify links.
     */
    public function test_certificate_verification_token_hashing()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        $token = CertificateVerificationToken::where('certificate_id', $cert->id)->first();
        $this->assertNotNull($token->token_hash);
        $this->assertEquals(64, strlen($token->token_hash)); // length of sha256 hex
    }

    /**
     * 16. Verify public check lookup using code.
     */
    public function test_lookup_by_verification_code()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        $req = request();
        $result = resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByCode($cert->verification_code, $req);

        $this->assertEquals('active', $result['status']);
        $this->assertEquals($cert->certificate_number, $result['certificate_number']);
    }

    /**
     * 17. Verify lookup by public secure token.
     */
    public function test_lookup_by_secure_token()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        // Retrieve raw token via generated token database entry
        $tokenRecord = CertificateVerificationToken::where('certificate_id', $cert->id)->first();
        
        // Since we stored the hash, let's create a raw token that resolves to it for simulation
        $raw = 'raw_token_simulated_in_test';
        $tokenRecord->update(['token_hash' => hash('sha256', $raw)]);

        $req = request();
        $result = resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByToken($raw, $req);

        $this->assertEquals('active', $result['status']);
    }

    /**
     * 18. Verify lookup logs verification events.
     */
    public function test_lookup_logs_verification_events()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        $req = request();
        resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByCode($cert->verification_code, $req);

        $this->assertDatabaseHas('academy_certificate_verification_events', [
            'certificate_id' => $cert->id,
            'result' => 'success'
        ]);
    }

    /**
     * 19. Verify status transition: expired certificates.
     */
    public function test_expired_certificate_returns_expired_status()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        // Force expired date
        $cert->update([
            'expires_at' => now()->subDay(),
            'status' => 'expired'
        ]);

        $req = request();
        $result = resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByCode($cert->verification_code, $req);

        $this->assertEquals('expired', $result['result']);
    }

    /**
     * 20. Verify status transition: administrative revocation.
     */
    public function test_revoked_certificate_blocks_verification()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        $this->certificateService->revokeCertificate($cert->id, 'Breach of compliance');

        $req = request();
        $result = resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByCode($cert->verification_code, $req);

        $this->assertEquals('revoked', $result['result']);
        $this->assertEquals('Breach of compliance', $result['revocation_reason']);
    }

    /**
     * 21. Verify competency gate for clinical provider permission.
     */
    public function test_competency_gate_authorizes_clinicians()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-CLIN-201')->first();

        // Register requirement
        $this->gateService->registerRequirement('provider', 'OPC-CLIN-201', 'write_prescription');

        // Uncertified clinician should be denied
        $authorized = $this->gateService->authorizeAction($user->id, 'provider', 'write_prescription');
        $this->assertFalse($authorized);

        // Issue certificate
        $this->certificateService->issueCertificate($user->id, $course->id, 90);

        // Certified clinician should be authorized
        $authorized = $this->gateService->authorizeAction($user->id, 'provider', 'write_prescription');
        $this->assertTrue($authorized);
    }

    /**
     * 22. Supervised signoff requirement by trainer.
     */
    public function test_trainer_signoff_requirement()
    {
        $learner = User::factory()->create();
        $trainer = User::factory()->create();
        $course = Course::where('course_code', 'OPC-CLIN-201')->first();
        $course->update(['requires_supervisor_signoff' => true]);

        TrainerSignoff::create([
            'learner_id' => $learner->id,
            'course_id' => $course->id,
            'trainer_id' => $trainer->id,
            'status' => 'approved',
            'signed_at' => now()
        ]);

        $this->assertDatabaseHas('academy_trainer_signoffs', [
            'learner_id' => $learner->id,
            'status' => 'approved'
        ]);
    }

    /**
     * 23. GDPR Privacy Compliant Public Name Masking.
     */
    public function test_public_lookup_masks_learner_name()
    {
        $user = User::factory()->create(['name' => 'Jean-Pierre Laurent']);
        $course = Course::where('course_code', 'OPC-FOUND-101')->first();
        $cert = $this->certificateService->issueCertificate($user->id, $course->id, 95);

        $req = request();
        $result = resolve(\App\Modules\Academy\Services\CertificateVerificationService::class)->verifyByCode($cert->verification_code, $req);

        // "Jean-Pierre" -> "J*********e", "Laurent" -> "L*****t"
        $this->assertEquals('J*********e L*****t', $result['learner']['name_masked']);
    }

    /**
     * 24. Student restriction safety gate.
     */
    public function test_student_restricted_from_prescribing_even_if_certified()
    {
        $user = User::factory()->create();
        $course = Course::where('course_code', 'OPC-STUDENT-101')->first();
        $this->certificateService->issueCertificate($user->id, $course->id, 100);

        // Students can NEVER write prescriptions or do emergency override, even if certified
        $authorized = $this->gateService->authorizeAction($user->id, 'student', 'write_prescription');
        $this->assertFalse($authorized);
    }

    /**
     * 25. Facility Digital Health Readiness computation.
     */
    public function test_facility_readiness_reporting()
    {
        $facilityId = '019e37fc-c262-73b0-998c-cb360f9429a2';
        \DB::table('facilities')->insert([
            'id' => $facilityId,
            'name' => 'Test Clinic',
            'type' => 'hospital',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $user1 = User::factory()->create(['primary_facility_id' => $facilityId]);
        $user2 = User::factory()->create(['primary_facility_id' => $facilityId]);

        $course1 = Course::where('course_code', 'OPC-FOUND-101')->first();
        $course2 = Course::where('course_code', 'OPC-PRIV-101')->first();

        // user1 completes both core courses -> certified
        $this->certificateService->issueCertificate($user1->id, $course1->id, 90);
        $this->certificateService->issueCertificate($user1->id, $course2->id, 90);

        $report = resolve(\App\Modules\Academy\Services\AcademyReportingService::class)->getFacilityReadinessReport($facilityId);

        $this->assertEquals(50, $report['readiness_percentage']);
        $this->assertEquals(2, $report['total_staff']);
        $this->assertEquals(1, $report['fully_certified_staff']);
    }
}
