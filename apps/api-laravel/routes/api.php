<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyIntegrationClient;
use App\Http\Middleware\IdempotencyProtection;

/*
|--------------------------------------------------------------------------
| OpesCare Operational Flow API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/operational-flow')->group(function () {
    Route::post('/patient-journey', [\App\Http\Controllers\Api\V1\OperationalFlowController::class, 'patientJourney']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Support and Helpdesk API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/support')->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\Api\V1\SupportController::class, 'index']);
    Route::post('/tickets', [\App\Http\Controllers\Api\V1\SupportController::class, 'store']);
    Route::post('/tickets/{ticket}/messages', [\App\Http\Controllers\Api\V1\SupportController::class, 'addMessage']);
    Route::post('/tickets/{ticket}/assign', [\App\Http\Controllers\Api\V1\SupportController::class, 'assign']);
    Route::post('/tickets/{ticket}/escalate', [\App\Http\Controllers\Api\V1\SupportController::class, 'escalate']);
    Route::post('/tickets/{ticket}/resolve', [\App\Http\Controllers\Api\V1\SupportController::class, 'resolve']);
    Route::post('/tickets/{ticket}/incident', [\App\Http\Controllers\Api\V1\SupportController::class, 'createIncident']);
    Route::post('/knowledge-base', [\App\Http\Controllers\Api\V1\SupportController::class, 'publishArticle']);
    Route::post('/knowledge-base/{article}/view', [\App\Http\Controllers\Api\V1\SupportController::class, 'viewArticle']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Billing and Cashier API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/billing')->group(function () {
    Route::get('/invoices', [\App\Http\Controllers\Api\V1\BillingController::class, 'invoices']);
    Route::post('/invoices', [\App\Http\Controllers\Api\V1\BillingController::class, 'createInvoice']);
    Route::post('/invoices/{invoice}/payments', [\App\Http\Controllers\Api\V1\BillingController::class, 'recordPayment']);
    Route::post('/payments/{payment}/refund', [\App\Http\Controllers\Api\V1\BillingController::class, 'refund']);
    Route::post('/wallets/deposit', [\App\Http\Controllers\Api\V1\BillingController::class, 'depositWallet']);
    Route::post('/cashier-sessions', [\App\Http\Controllers\Api\V1\BillingController::class, 'openSession']);
    Route::post('/cashier-sessions/{session}/close', [\App\Http\Controllers\Api\V1\BillingController::class, 'closeSession']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Queue and Patient Flow API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/queues')->group(function () {
    Route::get('/tickets', [\App\Http\Controllers\Api\V1\QueueController::class, 'index']);
    Route::post('/check-ins', [\App\Http\Controllers\Api\V1\QueueController::class, 'checkIn']);
    Route::post('/call-next', [\App\Http\Controllers\Api\V1\QueueController::class, 'callNext']);
    Route::get('/display', [\App\Http\Controllers\Api\V1\QueueController::class, 'display']);
    Route::post('/tickets/{ticket}/start-service', [\App\Http\Controllers\Api\V1\QueueController::class, 'startService']);
    Route::post('/tickets/{ticket}/transfer', [\App\Http\Controllers\Api\V1\QueueController::class, 'transfer']);
    Route::post('/tickets/{ticket}/prioritize', [\App\Http\Controllers\Api\V1\QueueController::class, 'prioritize']);
    Route::post('/tickets/{ticket}/complete', [\App\Http\Controllers\Api\V1\QueueController::class, 'complete']);
    Route::post('/tickets/{ticket}/cancel', [\App\Http\Controllers\Api\V1\QueueController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Appointment Booking API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/appointments')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'store']);
    Route::post('/no-shows', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'noShow']);
    Route::post('/{appointment}/reschedule', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'reschedule']);
    Route::post('/{appointment}/cancel', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'cancel']);
    Route::post('/{appointment}/check-in', [\App\Http\Controllers\Api\V1\AppointmentController::class, 'checkIn']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Connect Interoperability API Routes (B2B)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/connect')->group(function () {
    
    // Auth token request endpoint (unprotected by client middleware, uses POST body credentials)
    Route::post('/auth/token', [\App\Http\Controllers\Api\V1\Connect\AuthController::class, 'issueToken']);

    // Authenticated B2B routes group
    Route::middleware(VerifyIntegrationClient::class)->group(function () {
        
        // Widget session
        Route::post('/widget/sessions', [\App\Http\Controllers\Api\V1\Connect\AuthController::class, 'createWidgetSession']);

        // Secure patient search
        Route::post('/patients/search', [\App\Http\Controllers\Api\V1\Connect\PatientSearchController::class, 'search']);

        // Consent management
        Route::post('/consents/request', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'requestConsent']);
        Route::post('/consents/verify', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'verifyConsent']);
        Route::post('/emergency-access/request', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'requestEmergencyAccess']);
        Route::get('/patients/{health_id}/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'getEmergencyProfile']);

        // Record pulls
        Route::get('/patients/{health_id}/summary', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pullSummary']);
        Route::get('/patients/{health_id}/legacy-emergency-profile', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pullEmergencyProfile']);

        // Record writes (Protected by B2B Client Credentials + Idempotency middleware)
        Route::middleware(IdempotencyProtection::class)->group(function () {
            Route::post('/records/encounters', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushEncounter']);
            Route::post('/records/lab-results', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushLabResult']);
            Route::post('/records/prescriptions', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushPrescription']);
        });

        // Inventory Stock Sync
        Route::post('/inventory/pharmacy-stock/sync', [\App\Http\Controllers\Api\V1\Connect\InventoryController::class, 'syncPharmacyStock']);
        Route::post('/inventory/blood-stock/sync', [\App\Http\Controllers\Api\V1\Connect\InventoryController::class, 'syncBloodStock']);

        // Webhooks registration
        Route::post('/webhooks/subscriptions', [\App\Http\Controllers\Api\V1\Connect\WebhookController::class, 'createSubscription']);

        // Reconciliation cases
        Route::get('/reconciliation/cases', [\App\Http\Controllers\Api\V1\Connect\ReconciliationController::class, 'listCases']);
        Route::post('/reconciliation/cases/{id}/resolve', [\App\Http\Controllers\Api\V1\Connect\ReconciliationController::class, 'resolveCase']);
    });
});

/*
|--------------------------------------------------------------------------
| OpesCare Mobile Patient API Routes (B2C Private)
|--------------------------------------------------------------------------
*/
Route::prefix('mobile')->group(function () {
    
    // Auth & OTP verification
    Route::post('/auth/login', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'login']);
    Route::post('/auth/otp/verify', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'verifyOtp']);
    Route::post('/devices/register', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'registerDevice']);

    // Mock authenticated Patient scope
    Route::get('/me', [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getMe']);
    Route::get('/timeline', [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getTimeline']);

    // Patient Consent loop approvals
    Route::get('/consent-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listConsentRequests']);
    Route::post('/consent-requests/{id}/approve', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'approveConsent']);
    Route::post('/consent-requests/{id}/deny', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'denyConsent']);
    Route::post('/consents/{id}/revoke', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'revokeConsent']);

    // Access Logs B2C view
    Route::get('/access-logs', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listAccessLogs']);

    // Patient Correction filings
    Route::post('/correction-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'createCorrectionRequest']);

    // Patient data exports B2C
    Route::post('/data-export-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'createExportRequest']);
    Route::get('/data-export-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listExportRequests']);
    Route::get('/data-exports/{id}/download', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'downloadExport']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Public Health Reporting API Routes (Phases 1-4)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/public-health')->group(function () {
    // Phase 1: Drafts & Dashboards
    Route::get('/report-types', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getReportTypes']);
    Route::get('/reports', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getReports']);
    Route::get('/reports/{id}', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getReport']);
    Route::post('/reports/generate-drafts', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'generateDrafts']);
    Route::get('/reports/{id}/quality-checks', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getQualityChecks']);
    Route::get('/dashboard', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getDashboard']);
    Route::get('/facility-dashboard/{facility_id}', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getFacilityDashboard']);

    // Phase 2: Governance & Workflow Reviews
    Route::post('/reports/{id}/submit-for-review', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'submitForReview']);
    Route::post('/reports/{id}/assign', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'assignReport']);
    Route::post('/reports/{id}/approve', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'approveReport']);
    Route::post('/reports/{id}/request-correction', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'requestCorrection']);
    Route::post('/reports/{id}/reject', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'rejectReport']);
    Route::post('/reports/{id}/cancel', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'cancelReport']);
    Route::post('/reports/{id}/correct', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'correctReport']);
    Route::get('/reports/{id}/versions', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getVersions']);
    Route::get('/reports/{id}/status-history', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getStatusHistory']);
    Route::get('/review-queue', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getReviewQueue']);

    // Phase 3: Config, Submissions & Exports
    Route::get('/submission-profiles', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getSubmissionProfiles']);
    Route::post('/submission-profiles', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'createSubmissionProfile']);
    Route::post('/reports/{id}/submit', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'submitReport']);
    Route::post('/reports/{id}/export', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'exportReport']);
    Route::get('/exports/{id}/download', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'downloadExport']);
    Route::get('/integration-status', [\App\Http\Controllers\Api\V1\PublicHealth\PublicHealthController::class, 'getIntegrationStatus']);

    // Phase 4: Anomaly outbreak disease signals & trends
    Route::get('/signals', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'getSignals']);
    Route::get('/signals/{id}', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'getSignal']);
    Route::post('/signals/trigger-detection', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'triggerDetection']);
    Route::post('/signals/{id}/review', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'reviewSignal']);
    Route::get('/intelligence/dashboard', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'getIntelligenceDashboard']);
    Route::get('/intelligence/trends', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'getTrends']);
    Route::get('/intelligence/shortages', [\App\Http\Controllers\Api\V1\PublicHealth\IntelligenceController::class, 'getShortages']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Admin Data Governance & Compliance Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/admin')->group(function () {
    Route::get('/global-search', \App\Http\Controllers\Api\V1\Admin\GlobalSearchController::class);
    Route::get('/facilities/{facility}/go-live-readiness', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'show']);
    Route::post('/facilities/{facility}/go-live-readiness', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'store']);
    Route::patch('/facilities/{facility}/go-live-readiness/items/{item}', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'markItem']);
    Route::post('/facilities/{facility}/go-live-readiness/approve', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'approve']);
    Route::get('/access-logs', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listAccessLogs']);
    Route::get('/emergency-access/reviews', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listEmergencyAccessReviews']);
    Route::post('/emergency-access/{id}/review', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'reviewEmergencyAccess']);
    Route::get('/correction-requests', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listCorrectionRequests']);
    Route::post('/correction-requests/{id}/approve', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'approveCorrectionRequest']);
    Route::post('/correction-requests/{id}/reject', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'rejectCorrectionRequest']);
    Route::get('/data-export-requests', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listExportRequests']);
    Route::post('/data-export-requests/{id}/approve', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'approveExportRequest']);
    Route::post('/data-export-requests/{id}/reject', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'rejectExportRequest']);
    Route::get('/security-incidents', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listSecurityIncidents']);
    Route::post('/security-incidents', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'createSecurityIncident']);
    Route::post('/security-incidents/{id}/contain', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'containSecurityIncident']);
    Route::post('/security-incidents/{id}/resolve', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'resolveSecurityIncident']);
    Route::get('/country-policies', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'listCountryPolicies']);
    Route::post('/country-policies', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'createCountryPolicy']);
    Route::put('/country-policies/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'updateCountryPolicy']);
    Route::post('/country-policies/{id}/publish', [\App\Http\Controllers\Api\V1\Admin\AdminGovernanceController::class, 'publishCountryPolicy']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Connect API (V1)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/connect')->middleware(['api', 'throttle:verify'])->group(function () {
    Route::post('/medical-ids/verify', [\App\Http\Controllers\Api\V1\Connect\MedicalIdVerificationController::class, 'verifyHealthId']);
    Route::post('/medical-ids/verify-qr', [\App\Http\Controllers\Api\V1\Connect\MedicalIdVerificationController::class, 'verifyQr']);
    
    // Medical ID Phase 3
    Route::post('/consents/request-medical-id', [\App\Http\Controllers\Api\V1\Connect\ConsentController::class, 'requestConsent']);
    Route::post('/patients/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\EmergencyAccessController::class, 'pullEmergencyProfile']);

    // Medical ID Phase 4 - Duplicate Merge
    Route::get('/admin/merge-cases', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'listCases']);
    Route::post('/admin/merge-cases/{id}/resolve', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'resolveCase']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Demo API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('demo')->group(function () {
    Route::post('/reset', function () {
        if (!config('demo.enabled')) {
            abort(403, 'Demo mode disabled');
        }
        \Illuminate\Support\Facades\Artisan::call('opescare:demo:reset');
        return response()->json(['message' => 'Demo reset successfully']);
    });
    
    Route::post('/api/generate-temporary-secret', function () {
        if (!config('demo.enabled')) {
            abort(403, 'Demo mode disabled');
        }
        return response()->json(['secret' => 'demo_temp_secret_' . str()->random(16)]);
    });
});

require __DIR__.'/partners.php';
require __DIR__.'/communications.php';
require __DIR__.'/academy.php';

/*
|--------------------------------------------------------------------------
| OpesCare Referral Network API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/referrals')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'store']);
    Route::post('/expire-stale', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'expireStale']);
    Route::get('/{referral}', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'show']);
    Route::post('/{referral}/send', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'send']);
    Route::post('/{referral}/accept', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'accept']);
    Route::post('/{referral}/reject', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'reject']);
    Route::post('/{referral}/complete', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'complete']);
    Route::post('/{referral}/cancel', [\App\Http\Controllers\Api\V1\Referral\ReferralController::class, 'cancel']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Immunization API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/immunizations')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'store']);
    Route::post('/schedule', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'scheduleVaccines']);
    Route::get('/schedule', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'patientSchedule']);
    Route::get('/{immunization}', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'show']);
    Route::post('/{immunization}/adverse-reactions', [\App\Http\Controllers\Api\V1\Immunization\ImmunizationController::class, 'reportAdverseReaction']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Verifiable Document Template System V2 API Routes
|--------------------------------------------------------------------------
*/
Route::get('/v1/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'index']);
Route::post('/v1/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'store']);
Route::get('/v1/documents/{id}', [\App\Http\Controllers\Api\V1\DocumentController::class, 'show']);
Route::post('/v1/documents/{id}/amend', [\App\Http\Controllers\Api\V1\DocumentController::class, 'amend']);
Route::post('/v1/documents/{id}/revoke', [\App\Http\Controllers\Api\V1\DocumentController::class, 'revoke']);
Route::post('/v1/documents/{id}/entered-in-error', [\App\Http\Controllers\Api\V1\DocumentController::class, 'enteredInError']);
Route::post('/v1/document-verification/verify-code', [\App\Http\Controllers\Api\V1\DocumentController::class, 'verifyCode']);
Route::post('/v1/documents/{id}/share-links', [\App\Http\Controllers\Api\V1\DocumentController::class, 'share']);

/*
|--------------------------------------------------------------------------
| OpesCare Verified Care Access Map API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/care-map')->group(function () {
    Route::get('/facilities', [\App\Http\Controllers\Api\V1\CareMapController::class, 'index']);
    Route::get('/facilities/{id}', [\App\Http\Controllers\Api\V1\CareMapController::class, 'show']);
    Route::get('/search', [\App\Http\Controllers\Api\V1\CareMapController::class, 'index']);
    Route::get('/nearby', [\App\Http\Controllers\Api\V1\CareMapController::class, 'index']);
    Route::get('/pharmacies/medicine-search', [\App\Http\Controllers\Api\V1\CareMapController::class, 'searchMedicine']);
    Route::get('/labs/test-search', [\App\Http\Controllers\Api\V1\CareMapController::class, 'searchTests']);
    Route::get('/blood/search', [\App\Http\Controllers\Api\V1\CareMapController::class, 'searchBlood']);
    Route::get('/emergency', [\App\Http\Controllers\Api\V1\CareMapController::class, 'searchEmergency']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/facilities/{id}/save', [\App\Http\Controllers\Api\V1\CareMapController::class, 'saveFacility']);
        Route::post('/facilities/{id}/report', [\App\Http\Controllers\Api\V1\CareMapController::class, 'reportFacility']);
        Route::post('/facilities/{id}/claim', [\App\Http\Controllers\Api\V1\CareMapController::class, 'claimFacility']);
        
        // Partner syncs
        Route::post('/partner/facilities/{id}/stock-sync', [\App\Http\Controllers\Api\V1\CareMapController::class, 'partnerStockSync']);
        
        // Admin actions
        Route::post('/admin/facilities/{id}/verify', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminVerifyFacility']);
        Route::post('/admin/facilities/{id}/suspend', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminSuspendFacility']);
    });
});
