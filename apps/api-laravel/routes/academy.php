<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Academy\AcademyController;
use App\Http\Controllers\Api\V1\Academy\AcademyAdminController;

/*
|--------------------------------------------------------------------------
| OpesCare Academy API Routes
|--------------------------------------------------------------------------
*/

// Learner-facing routes — authenticated integration client (facility HIS, mobile app)
// [RBAC FIX] Previously had NO middleware — public access to enroll, submit quizzes, etc.
Route::prefix('v1/academy')->middleware(['verify.integration.client'])->group(function () {
    Route::get('/courses', [AcademyController::class, 'listCourses']);
    Route::get('/courses/{id}', [AcademyController::class, 'getCourse']);
    Route::post('/courses/{id}/enroll', [AcademyController::class, 'enroll']);
    Route::post('/lessons/{id}/complete', [AcademyController::class, 'completeLesson']);

    // Quizzes
    Route::post('/quizzes/{id}/start', [AcademyController::class, 'startQuiz']);
    Route::post('/quiz-attempts/{id}/submit', [AcademyController::class, 'submitQuiz']);

    // Simulations
    Route::post('/simulations/{courseId}/start', [AcademyController::class, 'startSimulation']);
    Route::post('/simulation-attempts/{id}/submit', [AcademyController::class, 'submitSimulation']);

    // Verification — public endpoint (certificate verification by external parties)
    // Pulled out of the authenticated group below so external verifiers can call it
});

// Public certificate verification — no auth required (external stakeholders verify certs)
Route::post('/v1/academy/certificate-verification/verify-code', [AcademyController::class, 'verifyCertificate']);

// Admin-only academy management — VerifyIntegrationClient + RequireApiAdminRole
// [RBAC FIX] Previously had NO middleware — seedTracks, revokeCertificate, etc. were public.
Route::prefix('v1/admin/academy')
    ->middleware(['verify.integration.client', 'api.admin'])
    ->group(function () {
        Route::post('/seed-tracks', [AcademyAdminController::class, 'seedTracks']);
        Route::post('/trainer-signoffs', [AcademyAdminController::class, 'approveTrainerSignoff']);
        Route::post('/competency-requirements', [AcademyAdminController::class, 'registerGate']);
        Route::get('/facility-readiness/{facilityId}', [AcademyAdminController::class, 'getFacilityReadiness']);
        Route::post('/certificates/{id}/revoke', [AcademyAdminController::class, 'revokeCertificate']);
        Route::post('/certificates/{id}/renew', [AcademyAdminController::class, 'renewCertificate']);
    });
