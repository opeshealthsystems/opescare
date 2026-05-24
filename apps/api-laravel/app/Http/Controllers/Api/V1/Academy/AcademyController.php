<?php

namespace App\Http\Controllers\Api\V1\Academy;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use App\Models\SimulationAttempt;
use App\Modules\Academy\Services\CertificateService;
use App\Modules\Academy\Services\CertificateVerificationService;
use App\Modules\Academy\Services\EnrollmentService;
use App\Modules\Academy\Services\QuizService;
use App\Modules\Academy\Services\SimulationService;
use Illuminate\Http\Request;

class AcademyController extends Controller
{
    protected $enrollmentService;
    protected $quizService;
    protected $simulationService;
    protected $certificateService;
    protected $verificationService;

    public function __construct(
        EnrollmentService $enrollmentService,
        QuizService $quizService,
        SimulationService $simulationService,
        CertificateService $certificateService,
        CertificateVerificationService $verificationService
    ) {
        $this->enrollmentService = $enrollmentService;
        $this->quizService = $quizService;
        $this->simulationService = $simulationService;
        $this->certificateService = $certificateService;
        $this->verificationService = $verificationService;
    }

    /**
     * List all published courses.
     */
    public function listCourses()
    {
        $courses = Course::where('status', 'published')->get();
        return response()->json($courses);
    }

    /**
     * Show a course.
     */
    public function getCourse(string $id)
    {
        $course = Course::with('modules.lessons', 'quizzes.questions')->findOrFail($id);
        return response()->json($course);
    }

    /**
     * Enroll a user.
     */
    public function enroll(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|string|max:255',
        ]);

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'USER_ID_REQUIRED'], 400);
        }

        try {
            $enrollment = $this->enrollmentService->enrollUser($userId, $id);
            return response()->json($enrollment, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Complete a lesson.
     */
    public function completeLesson(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|string|max:255',
        ]);

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'USER_ID_REQUIRED'], 400);
        }

        $progress = $this->enrollmentService->completeLesson($userId, $id);
        return response()->json($progress, 200);
    }

    /**
     * Start a quiz attempt.
     */
    public function startQuiz(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|string|max:255',
        ]);

        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'USER_ID_REQUIRED'], 400);
        }

        try {
            $attempt = $this->quizService->startQuiz($userId, $id);
            return response()->json($attempt, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Submit a quiz attempt.
     */
    public function submitQuiz(Request $request, string $id)
    {
        $request->validate([
            'answers' => 'nullable|array',
        ]);

        $answers = $request->input('answers', []);

        try {
            $attempt = $this->quizService->submitQuiz($id, $answers);

            // If passed, auto-issue certificate (unless requires simulation/supervisor)
            $quiz = $attempt->quiz;
            $course = $quiz->course;
            if ($attempt->status === 'passed') {
                $hasPassedSimulation = true;
                if ($course->requires_simulation) {
                    $hasPassedSimulation = SimulationAttempt::where('user_id', $attempt->user_id)
                        ->where('course_id', $course->id)
                        ->where('status', 'passed')
                        ->exists();
                }

                $hasSignoff = true;
                if ($course->requires_supervisor_signoff) {
                    $hasSignoff = \App\Models\TrainerSignoff::where('learner_id', $attempt->user_id)
                        ->where('course_id', $course->id)
                        ->where('status', 'approved')
                        ->exists();
                }

                if ($hasPassedSimulation && $hasSignoff) {
                    $this->certificateService->issueCertificate($attempt->user_id, $course->id, $attempt->score);
                }
            }

            return response()->json($attempt, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Start a simulation.
     */
    public function startSimulation(Request $request, string $courseId)
    {
        $request->validate([
            'user_id'         => 'required|string|max:255',
            'simulation_type' => 'nullable|string|max:50',
        ]);

        $userId = $request->input('user_id');
        $type = $request->input('simulation_type', 'EMR');
        if (!$userId) {
            return response()->json(['error' => 'USER_ID_REQUIRED'], 400);
        }

        $attempt = $this->simulationService->startSimulation($userId, $courseId, $type);
        return response()->json($attempt, 201);
    }

    /**
     * Submit a simulation.
     */
    public function submitSimulation(Request $request, string $id)
    {
        $request->validate([
            'actions' => 'nullable|array',
        ]);

        $actions = $request->input('actions', []);

        try {
            $attempt = $this->simulationService->submitSimulation($id, $actions);

            // If passed, check if they passed quiz and signoff as well to issue certificate
            $course = $attempt->course;
            if ($attempt->status === 'passed') {
                $hasPassedQuiz = QuizAttempt::where('user_id', $attempt->user_id)
                    ->whereIn('quiz_id', $course->quizzes->pluck('id'))
                    ->where('status', 'passed')
                    ->exists();

                $hasSignoff = true;
                if ($course->requires_supervisor_signoff) {
                    $hasSignoff = \App\Models\TrainerSignoff::where('learner_id', $attempt->user_id)
                        ->where('course_id', $course->id)
                        ->where('status', 'approved')
                        ->exists();
                }

                if ($hasPassedQuiz && $hasSignoff) {
                    $this->certificateService->issueCertificate($attempt->user_id, $course->id, $attempt->score);
                }
            }

            return response()->json($attempt, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Verify a certificate using secure token or verification code.
     */
    public function verifyCertificate(Request $request)
    {
        $request->validate([
            'verification_code' => 'nullable|string|max:255',
            'token'             => 'nullable|string|max:500',
        ]);

        $code = $request->input('verification_code');
        $token = $request->input('token');

        try {
            if ($token) {
                $result = $this->verificationService->verifyByToken($token, $request);
            } else if ($code) {
                $result = $this->verificationService->verifyByCode($code, $request);
            } else {
                return response()->json(['error' => 'VERIFICATION_CODE_OR_TOKEN_REQUIRED'], 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Render the visual public check portal in premium Dark Elite styling.
     */
    public function verifyPublic(Request $request, string $token)
    {
        try {
            $result = $this->verificationService->verifyByToken($token, $request);
            return view('academy.verify_certificate', compact('result'));
        } catch (\Exception $e) {
            return view('academy.verify_certificate', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Render the learner progress cockpit dashboard.
     */
    public function learnerDashboard(Request $request)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $enrollments = CourseEnrollment::where('user_id', $userId)->with('course')->get();
        $certificates = Certificate::where('user_id', $userId)->with('course')->get();

        return view('academy.learner_dashboard', compact('enrollments', 'certificates'));
    }
}
