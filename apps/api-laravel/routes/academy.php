<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Academy\AcademyController;
use App\Http\Controllers\Api\V1\Academy\AcademyAdminController;

/*
|--------------------------------------------------------------------------
| OpesCare Academy API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/academy')->group(function () {
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
    
    // Verification
    Route::post('/certificate-verification/verify-code', [AcademyController::class, 'verifyCertificate']);
});

Route::prefix('v1/admin/academy')->group(function () {
    Route::post('/seed-tracks', [AcademyAdminController::class, 'seedTracks']);
    Route::post('/trainer-signoffs', [AcademyAdminController::class, 'approveTrainerSignoff']);
    Route::post('/competency-requirements', [AcademyAdminController::class, 'registerGate']);
    Route::get('/facility-readiness/{facilityId}', [AcademyAdminController::class, 'getFacilityReadiness']);
    Route::post('/certificates/{id}/revoke', [AcademyAdminController::class, 'revokeCertificate']);
    Route::post('/certificates/{id}/renew', [AcademyAdminController::class, 'renewCertificate']);
});
