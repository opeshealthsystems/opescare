<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\VerifyIntegrationClient;
use App\Http\Middleware\IdempotencyProtection;

/*
|--------------------------------------------------------------------------
| Health Check — no authentication required
|--------------------------------------------------------------------------
*/
Route::get('/health', \App\Http\Controllers\Api\HealthCheckController::class);

/*
|--------------------------------------------------------------------------
| OpesCare Operational Flow API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/operational-flow')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/patient-journey', [\App\Http\Controllers\Api\V1\OperationalFlowController::class, 'patientJourney']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Support and Helpdesk API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/support')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::prefix('v1/billing')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::prefix('v1/queues')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::prefix('v1/appointments')->middleware(VerifyIntegrationClient::class)->group(function () {
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

    // Authenticated B2B routes group (per-client rate limit: 200 req/min)
    Route::middleware([VerifyIntegrationClient::class, 'throttle.client:200,1'])->group(function () {
        
        // Widget session
        Route::post('/widget/sessions', [\App\Http\Controllers\Api\V1\Connect\AuthController::class, 'createWidgetSession']);

        // Secure patient search
        Route::post('/patients/search', [\App\Http\Controllers\Api\V1\Connect\PatientSearchController::class, 'search']);

        // Health ID resolution — find or auto-create (key interoperability endpoint)
        Route::post('/patients/resolve', [\App\Http\Controllers\Api\V1\Connect\HealthIdResolutionController::class, 'resolve']);
        Route::get('/patients/verify/{health_id}', [\App\Http\Controllers\Api\V1\Connect\HealthIdResolutionController::class, 'verify']);

        // Consent management
        Route::post('/consents/request', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'requestConsent']);
        Route::post('/consents/verify', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'verifyConsent']);
        Route::post('/emergency-access/request', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'requestEmergencyAccess']);
        Route::get('/patients/{health_id}/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\ConnectGovernanceController::class, 'getEmergencyProfile']);

        // Record pulls (consent required — grant must include patients:read scope)
        Route::get('/patients/{health_id}/summary', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pullSummary'])
            ->middleware('consent.grant:patients:read');
        Route::get('/patients/{health_id}/legacy-emergency-profile', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pullEmergencyProfile']);

        // Record writes (Protected by: B2B auth → Idempotency key → Consent grant)
        Route::middleware(IdempotencyProtection::class)->group(function () {
            Route::post('/records/encounters', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushEncounter'])
                ->middleware('consent.grant:patients:write');
            Route::post('/records/lab-results', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushLabResult'])
                ->middleware('consent.grant:labs:write');
            Route::post('/records/prescriptions', [\App\Http\Controllers\Api\V1\Connect\RecordController::class, 'pushPrescription'])
                ->middleware('consent.grant:prescriptions:write');
        });

        // Inventory Stock Sync
        Route::post('/inventory/pharmacy-stock/sync', [\App\Http\Controllers\Api\V1\Connect\InventoryController::class, 'syncPharmacyStock']);
        Route::post('/inventory/blood-stock/sync', [\App\Http\Controllers\Api\V1\Connect\InventoryController::class, 'syncBloodStock']);

        // Webhooks
        Route::post('/webhooks/subscriptions', [\App\Http\Controllers\Api\V1\Connect\WebhookController::class, 'createSubscription']);
        Route::post('/webhooks/events/{eventId}/replay', [\App\Http\Controllers\Api\V1\Connect\WebhookController::class, 'replayEvent']);

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

    // Public auth endpoints — rate-limited to 5 requests per minute
    Route::prefix('auth')->middleware('throttle:5,1')->group(function () {
        // Legacy: phone + PIN → OTP flow
        Route::post('/login', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'login']);
        Route::post('/otp/verify', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'verifyOtp']);
        // Primary: email + password → direct token (same credentials as patient portal)
        Route::post('/login-email', [\App\Http\Controllers\Api\Mobile\MobileAuthController::class, 'loginWithCredentials']);
    });

    // Protected mobile endpoints — require valid patient Bearer token
    Route::middleware('auth.mobile')->group(function () {

        // Patient profile
        Route::get('/me', [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getMe']);
        Route::get('/timeline', [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getTimeline']);

        // Patient Consent loop approvals
        Route::get('/consent-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listConsentRequests']);
        Route::post('/consent-requests/{id}/approve', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'approveConsent']);
        Route::post('/consent-requests/{id}/deny', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'denyConsent']);
        Route::post('/consents/{id}/revoke', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'revokeConsent']);

        // Access Logs B2C view
        Route::get('/access-logs', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listAccessLogs']);

        // Limited encrypted offline mode
        Route::post('/offline/policies', [\App\Http\Controllers\Api\Mobile\OfflineSyncController::class, 'createPolicy']);
        Route::post('/offline/policies/{policy}/queue', [\App\Http\Controllers\Api\Mobile\OfflineSyncController::class, 'queue']);

        // Patient Correction filings
        Route::post('/correction-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'createCorrectionRequest']);

        // Patient data exports B2C
        Route::post('/data-export-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'createExportRequest']);
        Route::get('/data-export-requests', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'listExportRequests']);
        Route::get('/data-exports/{id}/download', [\App\Http\Controllers\Api\Mobile\MobileGovernanceController::class, 'downloadExport']);

        // Health-ID card (digital wallet)
        Route::get('/health-id-card',    [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getHealthIdCard']);

        // Clinical data (blood group, allergies, conditions, immunizations)
        Route::get('/allergies',         [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getAllergies']);
        Route::get('/clinical',          [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getClinical']);
        Route::get('/immunizations',     [\App\Http\Controllers\Api\Mobile\MobilePatientController::class, 'getImmunizations']);

        // Lab orders & results
        Route::get('/labs', [\App\Http\Controllers\Api\Mobile\MobileLabController::class, 'index']);
        Route::get('/labs/{id}', [\App\Http\Controllers\Api\Mobile\MobileLabController::class, 'show']);

        // Prescriptions
        Route::get('/prescriptions', [\App\Http\Controllers\Api\Mobile\MobilePrescriptionController::class, 'index']);
        Route::get('/prescriptions/{id}', [\App\Http\Controllers\Api\Mobile\MobilePrescriptionController::class, 'show']);

        // Appointments
        Route::get('/appointments', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'index']);
        Route::get('/appointments/{id}', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'show']);
        Route::post('/appointments', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'book']);
        Route::post('/appointments/{id}/cancel', [\App\Http\Controllers\Api\Mobile\MobileAppointmentController::class, 'cancel']);

        // Care facility directory
        Route::get('/facilities', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'index']);
        Route::get('/facilities/{id}', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'show']);
        Route::get('/facilities/{id}/slots', [\App\Http\Controllers\Api\Mobile\MobileFacilityController::class, 'slots']);

        // Official documents
        Route::get('/documents', [\App\Http\Controllers\Api\Mobile\MobileDocumentController::class, 'index']);
        Route::get('/documents/{id}', [\App\Http\Controllers\Api\Mobile\MobileDocumentController::class, 'show']);

        // App settings & push tokens
        Route::get('/settings', [\App\Http\Controllers\Api\Mobile\MobileSettingsController::class, 'show']);
        Route::patch('/settings', [\App\Http\Controllers\Api\Mobile\MobileSettingsController::class, 'update']);
        Route::post('/push-tokens', [\App\Http\Controllers\Api\Mobile\MobileSettingsController::class, 'registerPushToken']);
        Route::delete('/push-tokens/{id}', [\App\Http\Controllers\Api\Mobile\MobileSettingsController::class, 'revokePushToken']);
    });
});

/*
|--------------------------------------------------------------------------
| OpesCare Provider Mobile API Routes (B2B Private)
|--------------------------------------------------------------------------
*/
Route::prefix('provider-mobile')->group(function () {

    // Rate-limited auth endpoints (5 per minute = brute-force protection)
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/login', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'login']);
        Route::post('/auth/otp/verify', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'verifyOtp']);
    });

    // Public auth endpoints (no rate limiting)
    Route::post('/auth/push-token', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'registerPushToken']);
    Route::post('/auth/logout', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileAuthController::class, 'logout']);

    // Protected provider routes — require integration client credentials
    Route::middleware(VerifyIntegrationClient::class)->group(function () {
        Route::get('/facilities', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileFacilityController::class, 'index']);
        Route::get('/facilities/current', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileFacilityController::class, 'current']);
        Route::post('/facilities/{id}/switch', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileFacilityController::class, 'switchFacility']);

        Route::get('/patients/scan',              [\App\Http\Controllers\Api\ProviderMobile\ProviderMobilePatientController::class, 'scan']);
        Route::get('/patients/search',            [\App\Http\Controllers\Api\ProviderMobile\ProviderMobilePatientController::class, 'search']);
        Route::get('/patients/{id}/clinical',     [\App\Http\Controllers\Api\ProviderMobile\ProviderMobilePatientController::class, 'clinicalProfile']);
        Route::get('/patients/{id}',              [\App\Http\Controllers\Api\ProviderMobile\ProviderMobilePatientController::class, 'show']);

        Route::get('/tasks', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileTaskController::class, 'index']);
        Route::post('/tasks/queue/{id}/call', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileTaskController::class, 'callQueueEntry']);
        Route::post('/tasks/queue/{id}/complete', [\App\Http\Controllers\Api\ProviderMobile\ProviderMobileTaskController::class, 'completeQueueEntry']);
    });
});

/*
|--------------------------------------------------------------------------
| OpesCare Public Health Reporting API Routes (Phases 1-4)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/public-health')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::prefix('v1/admin')->middleware(VerifyIntegrationClient::class)->group(function () {
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
    Route::post('/patients/emergency-profile', [\App\Http\Controllers\Api\V1\Connect\EmergencyAccessController::class, 'pullEmergencyProfile'])
        ->middleware(VerifyIntegrationClient::class);

    // Medical ID Phase 4 - Duplicate Merge
    Route::get('/admin/merge-cases', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'listCases'])
        ->middleware(VerifyIntegrationClient::class);
    Route::post('/admin/merge-cases/{id}/resolve', [\App\Http\Controllers\Api\V1\Connect\DuplicateMergeController::class, 'resolveCase'])
        ->middleware(VerifyIntegrationClient::class);
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
});

require __DIR__.'/partners.php';
require __DIR__.'/communications.php';
require __DIR__.'/academy.php';

/*
|--------------------------------------------------------------------------
| OpesCare Referral Network API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/referrals')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::prefix('v1/immunizations')->middleware(VerifyIntegrationClient::class)->group(function () {
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
Route::middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/v1/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'index']);
    Route::post('/v1/documents', [\App\Http\Controllers\Api\V1\DocumentController::class, 'store']);
    Route::get('/v1/documents/{id}', [\App\Http\Controllers\Api\V1\DocumentController::class, 'show']);
    Route::post('/v1/documents/{id}/amend', [\App\Http\Controllers\Api\V1\DocumentController::class, 'amend']);
    Route::post('/v1/documents/{id}/revoke', [\App\Http\Controllers\Api\V1\DocumentController::class, 'revoke']);
    Route::post('/v1/documents/{id}/entered-in-error', [\App\Http\Controllers\Api\V1\DocumentController::class, 'enteredInError']);
    Route::post('/v1/document-verification/verify-code', [\App\Http\Controllers\Api\V1\DocumentController::class, 'verifyCode']);
    Route::post('/v1/documents/{id}/share-links', [\App\Http\Controllers\Api\V1\DocumentController::class, 'share']);
});

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
    });

    // Admin actions — require admin role
    Route::middleware(['auth', 'api.admin'])->group(function () {
        Route::post('/admin/facilities/{id}/verify', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminVerifyFacility']);
        Route::post('/admin/facilities/{id}/suspend', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminSuspendFacility']);
    });
});

/*
|--------------------------------------------------------------------------
| OpesCare SDK Token API Routes (Bearer token auth)
|--------------------------------------------------------------------------
*/
Route::prefix('v1/sdk')
    ->middleware(['sdk.token', 'throttle.client:120,1'])
    ->group(function () {

    // ── Patient lookup ────────────────────────────────────────
    Route::get('/patients/{health_id}/summary',
        [\App\Http\Controllers\Api\V1\Sdk\SdkPatientController::class, 'summary'])
        ->middleware('sdk.token:read_records');

    Route::get('/patients/{health_id}/encounters',
        [\App\Http\Controllers\Api\V1\Sdk\SdkPatientController::class, 'encounters'])
        ->middleware('sdk.token:read_records');

    // ── Facility data ──────────────────────────────────────────
    Route::get('/facilities/{id}',
        [\App\Http\Controllers\Api\V1\Sdk\SdkFacilityController::class, 'show'])
        ->middleware('sdk.token:read_facility');

    Route::get('/facilities/{id}/stock',
        [\App\Http\Controllers\Api\V1\Sdk\SdkFacilityController::class, 'stockSummary'])
        ->middleware('sdk.token:read_stock');

    // ── Appointments ────────────────────────────────────────────
    Route::post('/appointments',
        [\App\Http\Controllers\Api\V1\Sdk\SdkAppointmentController::class, 'book'])
        ->middleware('sdk.token:write_appointments');

    Route::get('/appointments/{id}',
        [\App\Http\Controllers\Api\V1\Sdk\SdkAppointmentController::class, 'show'])
        ->middleware('sdk.token:read_appointments');

    // ── Webhooks ────────────────────────────────────────────────
    Route::post('/webhooks/subscriptions',
        [\App\Http\Controllers\Api\V1\Sdk\SdkWebhookController::class, 'subscribe'])
        ->middleware('sdk.token:manage_webhooks');

    Route::delete('/webhooks/subscriptions/{id}',
        [\App\Http\Controllers\Api\V1\Sdk\SdkWebhookController::class, 'unsubscribe'])
        ->middleware('sdk.token:manage_webhooks');

    // ── Token introspection ─────────────────────────────────────
    Route::get('/token/introspect',
        [\App\Http\Controllers\Api\V1\Sdk\SdkAuthController::class, 'introspect']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Bridge Agent API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/bridge')
    ->middleware(['bridge.agent', 'throttle.client:300,1'])
    ->group(function () {
    Route::post('/sync',       [\App\Http\Controllers\Api\V1\Bridge\BridgeSyncController::class, 'sync']);
    Route::post('/heartbeat',  [\App\Http\Controllers\Api\V1\Bridge\BridgeSyncController::class, 'heartbeat']);
    Route::get('/status',      [\App\Http\Controllers\Api\V1\Bridge\BridgeSyncController::class, 'status']);
});

/*
|--------------------------------------------------------------------------
| OpesCare Lite Sync API Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1/lite')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/register-device',              [\App\Http\Controllers\Api\V1\LiteApiController::class, 'registerDevice']);
    Route::get('/config',                        [\App\Http\Controllers\Api\V1\LiteApiController::class, 'config']);
    Route::post('/sync/push',                    [\App\Http\Controllers\Api\V1\LiteApiController::class, 'syncPush']);
    Route::get('/sync/pull',                     [\App\Http\Controllers\Api\V1\LiteApiController::class, 'syncPull']);
    Route::post('/offline-events',               [\App\Http\Controllers\Api\V1\LiteApiController::class, 'offlineEvent']);
    // Gap 11 — conflict resolution endpoint
    Route::patch('/conflicts/{conflict}/resolve', [\App\Http\Controllers\Api\V1\LiteApiController::class, 'resolveConflict']);
    // Gap 12 — offline formulary download for prescription cache
    Route::get('/formulary',                     [\App\Http\Controllers\Api\V1\LiteApiController::class, 'formulary']);
});

/*
|--------------------------------------------------------------------------
| OpesCare FHIR R4 API Routes
| Read-only healthcare data interoperability layer
|--------------------------------------------------------------------------
*/
Route::prefix('fhir/R4')->group(function () {
    // CapabilityStatement — public per FHIR spec
    Route::get('/metadata', [\App\Http\Controllers\Api\Fhir\FhirController::class, 'metadata']);
});

Route::prefix('fhir/R4')->middleware(VerifyIntegrationClient::class)->group(function () {

    // Patient resource
    Route::get('/Patient',                [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchPatient']);
    Route::get('/Patient/{id}',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'patient']);
    Route::get('/Patient/{id}/\$everything', [\App\Http\Controllers\Api\Fhir\FhirController::class, 'patientEverything'])
        ->middleware('consent.grant:patients:read');

    // Encounter resource
    Route::get('/Encounter',              [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchEncounter']);
    Route::get('/Encounter/{id}',         [\App\Http\Controllers\Api\Fhir\FhirController::class, 'encounter']);

    // DiagnosticReport resource
    Route::get('/DiagnosticReport',       [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchDiagnosticReport']);
    Route::get('/DiagnosticReport/{id}',  [\App\Http\Controllers\Api\Fhir\FhirController::class, 'diagnosticReport']);

    // MedicationRequest resource
    Route::get('/MedicationRequest',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchMedicationRequest']);
    Route::get('/MedicationRequest/{id}', [\App\Http\Controllers\Api\Fhir\FhirController::class, 'medicationRequest']);

    // Practitioner
    Route::get('/Practitioner',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchPractitioner']);
    Route::get('/Practitioner/{id}',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'practitioner']);

    // Organization
    Route::get('/Organization',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchOrganization']);
    Route::get('/Organization/{id}',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'organization']);

    // DocumentReference
    Route::get('/DocumentReference',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchDocumentReference']);
    Route::get('/DocumentReference/{id}', [\App\Http\Controllers\Api\Fhir\FhirController::class, 'documentReference']);

    // Consent
    Route::get('/Consent',                [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchConsent']);
    Route::get('/Consent/{id}',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'consent']);

    // Coverage
    Route::get('/Coverage',               [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchCoverage']);
    Route::get('/Coverage/{id}',          [\App\Http\Controllers\Api\Fhir\FhirController::class, 'coverage']);

    // FHIR Immunization (ImmunizationRecord → FHIR Immunization)
    Route::get('/Immunization',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchImmunization']);
    Route::get('/Immunization/{id}',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'immunization']);

    // FHIR AllergyIntolerance (AllergyRecord → FHIR AllergyIntolerance)
    Route::get('/AllergyIntolerance',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchAllergyIntolerance']);
    Route::get('/AllergyIntolerance/{id}',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'allergyIntolerance']);

    // FHIR Condition (Diagnosis → FHIR Condition)
    Route::get('/Condition',              [\App\Http\Controllers\Api\Fhir\FhirController::class, 'searchCondition']);
    Route::get('/Condition/{id}',         [\App\Http\Controllers\Api\Fhir\FhirController::class, 'condition']);

    // FHIR Subscriptions (Item 11)
    Route::get('/Subscription',           [\App\Http\Controllers\Api\Fhir\FhirController::class, 'subscriptionIndex']);
    Route::post('/Subscription',          [\App\Http\Controllers\Api\Fhir\FhirController::class, 'subscriptionCreate']);
    Route::get('/Subscription/{id}',      [\App\Http\Controllers\Api\Fhir\FhirController::class, 'subscriptionShow']);
    Route::delete('/Subscription/{id}',   [\App\Http\Controllers\Api\Fhir\FhirController::class, 'subscriptionDelete']);

    // FHIR Bulk Export (Item 11)
    Route::get('/\$export',               [\App\Http\Controllers\Api\Fhir\FhirController::class, 'bulkExport']);
    Route::get('/Patient/{id}/\$export',  [\App\Http\Controllers\Api\Fhir\FhirController::class, 'patientBulkExport']);
});

/*
|--------------------------------------------------------------------------
| Insurance Claims & Preauthorization
|--------------------------------------------------------------------------
*/
Route::prefix('v1/insurance')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/eligibility/check', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'checkEligibility']);
    Route::post('/preauth', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'requestPreauth']);
    Route::post('/preauth/{id}/decide', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'decidePreauth']);
    Route::get('/claims', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'index']);
    Route::get('/claims/{id}', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'show']);
    Route::post('/claims/{id}/submit', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'submit']);
    Route::post('/claims/{id}/decide', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'decide']);
    Route::post('/claims/{id}/payment', [\App\Http\Controllers\Api\V1\InsuranceController::class, 'postPayment']);
});

/*
|--------------------------------------------------------------------------
| Triage & Emergency Workflow
|--------------------------------------------------------------------------
*/
Route::prefix('v1/triage')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\TriageController::class, 'listActive']);
    Route::post('/', [\App\Http\Controllers\Api\V1\TriageController::class, 'store']);
    Route::post('/{triageId}/score', [\App\Http\Controllers\Api\V1\TriageController::class, 'score']);
    Route::post('/{triageId}/reassess', [\App\Http\Controllers\Api\V1\TriageController::class, 'reassess']);
    Route::post('/{triageId}/escalate', [\App\Http\Controllers\Api\V1\TriageController::class, 'escalateToEmergency']);
});

/*
|--------------------------------------------------------------------------
| Inventory & Supply Chain
|--------------------------------------------------------------------------
*/
Route::prefix('v1/inventory')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\InventoryController::class, 'index']);
    Route::patch('/{itemId}/stock', [\App\Http\Controllers\Api\V1\InventoryController::class, 'updateStock']);
    Route::get('/low-stock', [\App\Http\Controllers\Api\V1\InventoryController::class, 'getLowStockItems']);
    Route::post('/purchase-orders', [\App\Http\Controllers\Api\V1\InventoryController::class, 'createPurchaseOrder']);
    Route::post('/purchase-orders/{orderId}/receive', [\App\Http\Controllers\Api\V1\InventoryController::class, 'receiveGoods']);
    Route::post('/audits', [\App\Http\Controllers\Api\V1\InventoryController::class, 'openAudit']);
    Route::post('/audits/{auditId}/close', [\App\Http\Controllers\Api\V1\InventoryController::class, 'closeAudit']);
});

/*
|--------------------------------------------------------------------------
| Analytics & Reporting
|--------------------------------------------------------------------------
*/
Route::prefix('v1/analytics')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/facilities/{facilityId}/dashboard', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'facilityDashboard']);
    Route::get('/appointments', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'appointmentStats']);
    Route::get('/queues', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'queueStats']);
    Route::get('/billing', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'billingStats']);
    Route::post('/exports', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'requestExport']);
    Route::get('/exports/{exportId}', [\App\Http\Controllers\Api\V1\AnalyticsController::class, 'exportStatus']);
});

/*
|--------------------------------------------------------------------------
| Staff, HR & Shift Management
|--------------------------------------------------------------------------
*/
Route::prefix('v1/staff')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\V1\StaffController::class, 'index']);
    // Static paths must be declared before wildcard /{staffId} to avoid shadowing
    Route::get('/rosters', [\App\Http\Controllers\Api\V1\StaffController::class, 'getRoster']);
    Route::post('/shifts', [\App\Http\Controllers\Api\V1\StaffController::class, 'assignShift']);
    Route::delete('/shifts/{shiftId}', [\App\Http\Controllers\Api\V1\StaffController::class, 'removeShift']);
    Route::post('/leave', [\App\Http\Controllers\Api\V1\StaffController::class, 'requestLeave']);
    Route::post('/leave/{leaveId}/approve', [\App\Http\Controllers\Api\V1\StaffController::class, 'approveLeave']);
    Route::post('/leave/{leaveId}/reject', [\App\Http\Controllers\Api\V1\StaffController::class, 'rejectLeave']);
    Route::get('/{staffId}', [\App\Http\Controllers\Api\V1\StaffController::class, 'show']);
    Route::patch('/{staffId}', [\App\Http\Controllers\Api\V1\StaffController::class, 'updateProfile']);
});

/*
|--------------------------------------------------------------------------
| Telemedicine
|--------------------------------------------------------------------------
*/
Route::prefix('v1/telemedicine')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/consultations', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'book']);
    Route::get('/consultations/{consultId}', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'show']);
    Route::post('/consultations/{consultId}/cancel', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'cancel']);
    Route::post('/consultations/{consultId}/consent', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'recordConsent']);
    Route::post('/consultations/{consultId}/waiting-room', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'joinWaitingRoom']);
    Route::post('/consultations/{consultId}/call', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'initiateCall']);
    Route::post('/sessions/{sessionId}/end', [\App\Http\Controllers\Api\V1\TelemedicineController::class, 'endCall']);
});

/*
|--------------------------------------------------------------------------
| Ward, Admission & Bed Management
|--------------------------------------------------------------------------
*/
Route::prefix('v1/ward')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('/admissions', [\App\Http\Controllers\Api\V1\WardController::class, 'admit']);
    Route::post('/admissions/{admissionId}/assign-bed', [\App\Http\Controllers\Api\V1\WardController::class, 'assignBed']);
    Route::post('/admissions/{admissionId}/transfer', [\App\Http\Controllers\Api\V1\WardController::class, 'transferBed']);
    Route::post('/admissions/{admissionId}/discharge', [\App\Http\Controllers\Api\V1\WardController::class, 'discharge']);
    Route::post('/admissions/{admissionId}/nursing-round', [\App\Http\Controllers\Api\V1\WardController::class, 'recordNursingRound']);
    Route::post('/admissions/{admissionId}/discharge-plan', [\App\Http\Controllers\Api\V1\WardController::class, 'initiateDischargePlan']);
    Route::get('/beds/availability', [\App\Http\Controllers\Api\V1\WardController::class, 'getBedAvailability']);
});

/*
|--------------------------------------------------------------------------
| Security Operations Center
|--------------------------------------------------------------------------
*/
Route::prefix('v1/security')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/audit-log', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'searchAuditLog']);
    Route::get('/suspicious-flags', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'listSuspiciousFlags']);
    Route::post('/suspicious-flags/{flagId}/review', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'reviewFlag']);
    Route::post('/breaches', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'openBreach']);
    Route::post('/breaches/{breachId}/notify', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'markBreachNotified']);
    Route::post('/breaches/{breachId}/close', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'closeBreach']);
    Route::post('/access-reviews', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'initiateAccessReview']);
    Route::post('/access-reviews/{reviewId}/complete', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'completeAccessReview']);
    Route::post('/compliance-exports', [\App\Http\Controllers\Api\V1\SecurityOperationsController::class, 'requestComplianceExport']);
});

/*
|--------------------------------------------------------------------------
| Subscription & SaaS Billing
|--------------------------------------------------------------------------
*/
Route::prefix('v1/subscriptions')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('/plans', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'listPlans']);
    Route::get('/plans/{planId}', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'showPlan']);
    Route::get('/my', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'getMySubscription']);
    Route::post('/', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'subscribe']);
    Route::post('/upgrade', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'upgrade']);
    Route::post('/cancel', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'cancel']);
    Route::get('/usage', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'getUsage']);
    Route::get('/limits/{featureKey}', [\App\Http\Controllers\Api\V1\SubscriptionController::class, 'checkLimit']);
});

/*
|--------------------------------------------------------------------------
| Maternity & Antenatal Care
|--------------------------------------------------------------------------
*/
Route::prefix('v1/maternity')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('patients/{patientId}/pregnancies',   [\App\Http\Controllers\Api\V1\MaternityController::class, 'index']);
    Route::post('patients/{patientId}/pregnancies',  [\App\Http\Controllers\Api\V1\MaternityController::class, 'store']);
    Route::get('pregnancies/{id}',                   [\App\Http\Controllers\Api\V1\MaternityController::class, 'show']);
    Route::get('pregnancies/{id}/antenatal-visits',  [\App\Http\Controllers\Api\V1\MaternityController::class, 'antenatalVisits']);
    Route::post('pregnancies/{id}/antenatal-visits', [\App\Http\Controllers\Api\V1\MaternityController::class, 'storeAntenatalVisit']);
    Route::get('pregnancies/{id}/deliveries',        [\App\Http\Controllers\Api\V1\MaternityController::class, 'deliveries']);
    Route::post('pregnancies/{id}/deliveries',       [\App\Http\Controllers\Api\V1\MaternityController::class, 'storeDelivery']);
});

/*
|--------------------------------------------------------------------------
| Provider Performance Reports
|--------------------------------------------------------------------------
*/
Route::prefix('v1/reports/providers')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('{providerId}/performance',          [\App\Http\Controllers\Api\V1\Reports\ProviderPerformanceController::class, 'summary']);
    Route::get('{providerId}/top-diagnoses',        [\App\Http\Controllers\Api\V1\Reports\ProviderPerformanceController::class, 'topDiagnoses']);
    Route::get('facility/{facilityId}/performance', [\App\Http\Controllers\Api\V1\Reports\ProviderPerformanceController::class, 'facilitySummary']);
});

/*
|--------------------------------------------------------------------------
| Revenue Cycle Reports
|--------------------------------------------------------------------------
*/
Route::prefix('v1/reports/revenue-cycle')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('summary', [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'summary']);
    Route::get('aging',   [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'aging']);
    Route::get('denials', [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'denials']);
    Route::get('trend',   [\App\Http\Controllers\Api\V1\Reports\RevenueCycleController::class, 'trend']);
});

/*
|--------------------------------------------------------------------------
| Patient Payment Plans
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('payment-plans',                                          [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'store']);
    Route::get('payment-plans/{id}',                                      [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'show']);
    Route::post('payment-plans/{id}/installments/{installmentId}/pay',   [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'recordPayment']);
    Route::get('patients/{patientId}/payment-plans',                      [\App\Http\Controllers\Api\V1\PatientPaymentPlanController::class, 'forPatient']);
});

/*
|--------------------------------------------------------------------------
| Radiology Reports
|--------------------------------------------------------------------------
*/
Route::prefix('v1/radiology')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('reports',                                          [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'store']);
    Route::get('reports/{id}',                                      [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'show']);
    Route::patch('reports/{id}/finalize',                           [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'finalize']);
    Route::patch('reports/{id}/amend',                              [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'amend']);
    Route::post('reports/{id}/distribute',                          [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'distribute']);
    Route::get('facilities/{facilityId}/reports/pending',           [\App\Http\Controllers\Api\V1\RadiologyReportController::class, 'pending']);
});

/*
|--------------------------------------------------------------------------
| Drug Formulary
|--------------------------------------------------------------------------
*/
Route::prefix('v1/pharmacy/formulary')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('search',              [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'search']);
    Route::get('controlled',          [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'controlled']);
    Route::post('/',                  [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'store']);
    Route::patch('{id}/availability', [\App\Http\Controllers\Api\V1\DrugFormularyController::class, 'toggleAvailability']);
});

/*
|--------------------------------------------------------------------------
| Controlled Substance Dispensing & Inventory
|--------------------------------------------------------------------------
*/
Route::prefix('v1/pharmacy/controlled-substances')->middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('dispense',          [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'dispense']);
    Route::post('{id}/witness',      [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'confirmWitness']);
    Route::post('reconcile',         [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'reconcile']);
    Route::get('log',                [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'log']);
    Route::get('inventory',          [\App\Http\Controllers\Api\V1\ControlledSubstanceController::class, 'inventory']);
});

/*
|--------------------------------------------------------------------------
| USSD — Africa's Talking webhook (no auth required)
|--------------------------------------------------------------------------
*/
Route::post('/ussd/callback', [\App\Http\Controllers\Api\Ussd\UssdController::class, 'callback']);

/*
|--------------------------------------------------------------------------
| Care Plans — clinician routes
|--------------------------------------------------------------------------
*/
Route::middleware(VerifyIntegrationClient::class)->group(function () {
    Route::post('v1/care-plans',                                        [\App\Http\Controllers\Api\V1\CarePlanController::class, 'store']);
    Route::get('v1/care-plans/{id}',                                    [\App\Http\Controllers\Api\V1\CarePlanController::class, 'show']);
    Route::post('v1/care-plans/{id}/goals',                             [\App\Http\Controllers\Api\V1\CarePlanController::class, 'storeGoal']);
    Route::patch('v1/care-plans/{id}/goals/{goalId}',                   [\App\Http\Controllers\Api\V1\CarePlanController::class, 'updateGoal']);
    Route::post('v1/care-plans/{id}/interventions',                     [\App\Http\Controllers\Api\V1\CarePlanController::class, 'storeIntervention']);
});

/*
|--------------------------------------------------------------------------
| Patient Satisfaction Survey — report endpoint
|--------------------------------------------------------------------------
*/
Route::middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('v1/reports/surveys/satisfaction', [\App\Http\Controllers\Api\V1\Reports\SurveyReportController::class, 'satisfaction']);
});

/*
|--------------------------------------------------------------------------
| Mobile: Care Plans, Surveys, Medical Record Export (patient-facing)
|--------------------------------------------------------------------------
*/
/*
|--------------------------------------------------------------------------
| Advance Directives — clinician/admin
|--------------------------------------------------------------------------
*/
Route::middleware(VerifyIntegrationClient::class)->group(function () {
    Route::get('v1/patients/{patientId}/advance-directives',         [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'index']);
    Route::post('v1/patients/{patientId}/advance-directives',        [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'store']);
    Route::get('v1/patients/{patientId}/advance-directives/{id}',    [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'show']);
    Route::delete('v1/patients/{patientId}/advance-directives/{id}', [\App\Http\Controllers\Api\V1\AdvanceDirectiveController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| Pen Test Tracker — security/admin
|--------------------------------------------------------------------------
*/
Route::middleware(VerifyIntegrationClient::class)->prefix('v1/security')->group(function () {
    Route::get('pen-tests',                                     [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'index']);
    Route::post('pen-tests',                                    [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'store']);
    Route::get('pen-tests/open-findings',                       [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'openFindings']);
    Route::get('pen-tests/{id}',                                [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'show']);
    Route::post('pen-tests/{id}/findings',                      [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'storeFinding']);
    Route::patch('pen-tests/{id}/findings/{findingId}',         [\App\Http\Controllers\Api\V1\Security\PenTestController::class, 'updateFinding']);
});

Route::prefix('mobile')->middleware('auth.mobile')->group(function () {
    // Care plans (read-only for patient)
    Route::get('care-plans',      [\App\Http\Controllers\Api\Mobile\MobileCarePlanController::class, 'index']);
    Route::get('care-plans/{id}', [\App\Http\Controllers\Api\Mobile\MobileCarePlanController::class, 'show']);

    // Satisfaction surveys
    Route::get('surveys',                  [\App\Http\Controllers\Api\Mobile\MobileSurveyController::class, 'index']);
    Route::get('surveys/{id}',             [\App\Http\Controllers\Api\Mobile\MobileSurveyController::class, 'show']);
    Route::post('surveys/{id}/submit',     [\App\Http\Controllers\Api\Mobile\MobileSurveyController::class, 'submit']);

    // Medical record export
    Route::post('medical-records/export/pdf',  [\App\Http\Controllers\Api\Mobile\MedicalRecordExportController::class, 'exportPdf']);
    Route::post('medical-records/export/fhir', [\App\Http\Controllers\Api\Mobile\MedicalRecordExportController::class, 'exportFhir']);
});
