<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPageController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

// Root / Landing
Route::get('/', [PublicPageController::class, 'index'])->name('public.landing');

// Core Institutional Pages
Route::get('/about', [PublicPageController::class, 'about'])->name('public.about');
Route::get('/how-it-works', [PublicPageController::class, 'howItWorks'])->name('public.how-it-works');

// Solutions
Route::prefix('solutions')->group(function () {
    Route::get('/patients', [PublicPageController::class, 'solutionsPatients'])->name('public.solutions.patients');
    Route::get('/hospitals', [PublicPageController::class, 'solutionsHospitals'])->name('public.solutions.hospitals');
    Route::get('/pharmacies', [PublicPageController::class, 'solutionsPharmacies'])->name('public.solutions.pharmacies');
    Route::get('/laboratories', [PublicPageController::class, 'solutionsLaboratories'])->name('public.solutions.laboratories');
    Route::get('/insurers', [PublicPageController::class, 'solutionsInsurers'])->name('public.solutions.insurers');
    Route::get('/public-health', [PublicPageController::class, 'solutionsPublicHealth'])->name('public.solutions.public-health');
});

// Tech & Dev
Route::get('/interoperability', [PublicPageController::class, 'interoperability'])->name('public.interoperability');
Route::get('/developers', [PublicPageController::class, 'developers'])->name('public.developers');
Route::get('/security', [PublicPageController::class, 'security'])->name('public.security');

// Legal & Privacy
Route::get('/privacy', [PublicPageController::class, 'privacy'])->name('public.privacy');
Route::get('/terms', [PublicPageController::class, 'terms'])->name('public.terms');
Route::get('/consent', [PublicPageController::class, 'consent'])->name('public.consent');

// Support
Route::get('/faq', [PublicPageController::class, 'faq'])->name('public.faq');
Route::get('/help', [PublicPageController::class, 'help'])->name('public.help');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('public.contact');
Route::post('/contact', [PublicPageController::class, 'contactSubmit'])->name('public.contact.submit');
Route::get('/status', [PublicPageController::class, 'status'])->name('public.status');

// Onboarding / Auth Path Selectors & Tailored Routes
Route::get('/signup', [PublicPageController::class, 'showRegisterSelector'])->name('register');
Route::get('/register', function() { return redirect()->route('register'); });

// Patient Onboarding
Route::get('/signup/patient', [PublicPageController::class, 'showPatientRegister'])->name('register.patient');
Route::post('/signup/patient', [PublicPageController::class, 'submitPatientRegister'])->name('register.patient.submit');
Route::get('/register/patient', function() { return redirect()->route('register.patient'); });

// Guardian Caregiver Requests
Route::get('/signup/guardian', [PublicPageController::class, 'showGuardianRegister'])->name('register.guardian');
Route::post('/signup/guardian', [PublicPageController::class, 'submitGuardianRegister'])->name('register.guardian.submit');

// Organization Onboarding Form
Route::get('/signup/organization', [PublicPageController::class, 'showOrganizationRegister'])->name('register.organization');
Route::post('/signup/organization', [PublicPageController::class, 'submitOrganizationRegister'])->name('register.organization.submit');
Route::get('/register/hospital', function() { return redirect()->route('register.organization'); });

// Developer API Requests
Route::get('/signup/developer', [PublicPageController::class, 'showDeveloperRegister'])->name('register.developer');
Route::post('/signup/developer', [PublicPageController::class, 'submitDeveloperRegister'])->name('register.developer.submit');

// Staff Invitation Activation
Route::get('/invite/{token}', [PublicPageController::class, 'showStaffInvite'])->name('invite.accept');
Route::post('/invite/{token}', [PublicPageController::class, 'submitStaffInvite'])->name('invite.accept.submit');

// Password Recovery & Credential Update
Route::get('/forgot-password', [PublicPageController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [PublicPageController::class, 'submitForgotPassword'])->name('password.email');
Route::get('/reset-password/{token}', [PublicPageController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password/{token}', [PublicPageController::class, 'submitResetPassword'])->name('password.update');

// OTP Screen Challenge
Route::get('/verify/otp', [PublicPageController::class, 'showVerifyOtp'])->name('otp.verify');
Route::post('/verify/otp', [PublicPageController::class, 'submitVerifyOtp'])->name('otp.verify.submit');
Route::post('/verify/otp/resend', [PublicPageController::class, 'resendOtp'])->name('otp.resend');

// Verification & Restriction Status Displays
Route::get('/pending-approval', [PublicPageController::class, 'showPendingApproval'])->name('account.pending');
Route::get('/account-suspended', [PublicPageController::class, 'showAccountSuspended'])->name('account.suspended');

// Multi-Facility Access Plane Selector
Route::get('/select-facility', [PublicPageController::class, 'showSelectFacility'])->name('select-facility');
Route::post('/select-facility', [PublicPageController::class, 'submitSelectFacility'])->name('select-facility.submit');

// Secure Portal Access Override
Route::get('/login', [PublicPageController::class, 'showLogin'])->name('login');
Route::post('/login', [PublicPageController::class, 'submitLogin'])->name('login.submit');

// Session / Logout
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login')->with('success', 'You have been signed out securely.');
})->name('logout');
Route::get('/logout', function () {
    return redirect()->route('login');
});

// Localization Switcher
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'fr'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Demo Access Routes
Route::middleware(['web'])->group(function () {
    Route::get('/demo-access', [\App\Http\Controllers\Demo\DemoAccessController::class, 'index'])->name('demo.index');
    Route::get('/demo-access/public', [\App\Http\Controllers\Demo\DemoAccessController::class, 'publicDemo'])->name('demo.public');
    Route::get('/demo-access/internal', [\App\Http\Controllers\Demo\DemoAccessController::class, 'internalDemo'])->name('demo.internal');
    Route::post('/demo-access/login-as', [\App\Http\Controllers\Demo\DemoAccessController::class, 'loginAs'])->name('demo.login-as');
    
    Route::get('/dashboard', function() {
        return view('demo.dashboard');
    })->name('dashboard')->middleware('auth');
});

// Public Medical ID Verification Routes
Route::middleware(['web', 'throttle:verify'])->group(function () {
    Route::get('/verify/health-id', [\App\Http\Controllers\MedicalId\VerifyController::class, 'healthId'])->name('verify.health-id');
    Route::post('/verify/health-id', [\App\Http\Controllers\MedicalId\VerifyController::class, 'healthIdLookup'])->name('verify.health-id.lookup');
    Route::get('/verify/qr/{token}', [\App\Http\Controllers\MedicalId\VerifyController::class, 'qr'])->name('verify.qr');
});

// Portal Placeholders
Route::middleware(['web'])->group(function () {
    Route::get('/portals/patient', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'index'])->name('portals.patient');
    Route::get('/portals/patient/logs', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'accessLogs'])->name('portals.patient.logs');
    Route::post('/portals/patient/generate-qr', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'generateTemporaryQr'])->name('portals.patient.qr');

    Route::get('/portals/staff', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'index'])->name('portals.staff');
    Route::get('/portals/staff/appointments', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointments'])->name('portals.staff.appointments');
    Route::get('/portals/staff/queue', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queue'])->name('portals.staff.queue');
    Route::get('/portals/staff/billing', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'billing'])->name('portals.staff.billing');
    Route::get('/portals/staff/support', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'support'])->name('portals.staff.support');

    Route::get('/portals/staff/referrals', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referrals'])->name('portals.staff.referrals');
    Route::get('/portals/staff/referrals/create', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsCreate'])->name('portals.staff.referrals.create');
    Route::post('/portals/staff/referrals', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsStore'])->name('portals.staff.referrals.store');
    Route::get('/portals/staff/referrals/{id}', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsShow'])->name('portals.staff.referrals.show');
    Route::post('/portals/staff/referrals/{id}/send', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsSend'])->name('portals.staff.referrals.send');
    Route::post('/portals/staff/referrals/{id}/accept', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsAccept'])->name('portals.staff.referrals.accept');
    Route::post('/portals/staff/referrals/{id}/reject', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsReject'])->name('portals.staff.referrals.reject');
    Route::post('/portals/staff/referrals/{id}/complete', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsComplete'])->name('portals.staff.referrals.complete');
    Route::post('/portals/staff/referrals/{id}/cancel', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'referralsCancel'])->name('portals.staff.referrals.cancel');

    Route::get('/portals/staff/queue-display', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queueDisplay'])->name('portals.staff.queue-display');

    Route::get('/portals/staff/immunizations', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'immunizations'])->name('portals.staff.immunizations');
    Route::get('/portals/staff/immunizations/record', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'immunizationsRecord'])->name('portals.staff.immunizations.record');
    Route::post('/portals/staff/immunizations', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'immunizationsStore'])->name('portals.staff.immunizations.store');

    Route::get('/portals/patient/appointments', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'appointments'])->name('portals.patient.appointments');

    // ── Staff: Appointment actions ──────────────────────────────
    Route::get('/portals/staff/appointments/create', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsCreate'])->name('portals.staff.appointments.create');
    Route::post('/portals/staff/appointments', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsStore'])->name('portals.staff.appointments.store');
    Route::post('/portals/staff/appointments/{id}/confirm', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsConfirm'])->name('portals.staff.appointments.confirm');
    Route::post('/portals/staff/appointments/{id}/cancel', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsCancel'])->name('portals.staff.appointments.cancel');
    Route::post('/portals/staff/appointments/{id}/check-in', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsCheckIn'])->name('portals.staff.appointments.check-in');
    Route::post('/portals/staff/appointments/{id}/no-show', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'appointmentsNoShow'])->name('portals.staff.appointments.no-show');

    // ── Staff: Queue actions ─────────────────────────────────────
    Route::post('/portals/staff/queue/check-in', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queueCheckIn'])->name('portals.staff.queue.check-in');
    Route::post('/portals/staff/queue/{id}/call', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queueCall'])->name('portals.staff.queue.call');
    Route::post('/portals/staff/queue/{id}/start', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queueStart'])->name('portals.staff.queue.start');
    Route::post('/portals/staff/queue/{id}/complete', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'queueComplete'])->name('portals.staff.queue.complete');

    // ── Staff: Billing actions ───────────────────────────────────
    Route::get('/portals/staff/billing/create', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'billingCreate'])->name('portals.staff.billing.create');
    Route::post('/portals/staff/billing', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'billingStore'])->name('portals.staff.billing.store');
    Route::post('/portals/staff/billing/{id}/pay', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'billingPay'])->name('portals.staff.billing.pay');

    // ── Staff: Support actions ───────────────────────────────────
    Route::post('/portals/staff/support', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'supportStore'])->name('portals.staff.support.store');
    Route::post('/portals/staff/support/{id}/reply', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'supportReply'])->name('portals.staff.support.reply');
    Route::post('/portals/staff/support/{id}/close', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'supportClose'])->name('portals.staff.support.close');

    Route::get('/portals/admin', [\App\Http\Controllers\MedicalId\AdminPortalController::class, 'index'])->name('portals.admin');
    Route::get('/portals/admin/go-live', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'index'])->name('portals.admin.go-live');
});

/*
|--------------------------------------------------------------------------
| OpesCare Verifiable Document Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/verify/document/{token}', [\App\Http\Controllers\Api\V1\DocumentController::class, 'verifyPublic'])->name('document.verify');
Route::get('/documents/{id}/view', [\App\Http\Controllers\Api\V1\DocumentController::class, 'renderDocument'])->name('document.render');
Route::get('/share/document/{token}', function ($token) {
    try {
        $document = resolve(\App\Services\Documents\DocumentShareService::class)->resolveShareLink($token);
        return redirect()->route('document.render', ['id' => $document->id]);
    } catch (\Exception $e) {
        abort(404, $e->getMessage());
    }
})->name('document.share.view');

/*
|--------------------------------------------------------------------------
| OpesCare Academy Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/verify/certificate/{token}', [\App\Http\Controllers\Api\V1\Academy\AcademyController::class, 'verifyPublic'])->name('academy.certificate.verify');
Route::get('/academy/dashboard', [\App\Http\Controllers\Api\V1\Academy\AcademyController::class, 'learnerDashboard'])->name('academy.dashboard');
Route::get('/admin/academy/readiness/{facilityId}', [\App\Http\Controllers\Api\V1\Academy\AcademyAdminController::class, 'readinessDashboard'])->name('academy.admin.readiness');

/*
|--------------------------------------------------------------------------
| OpesCare Verified Care Access Map Web Routes
|--------------------------------------------------------------------------
*/
Route::get('/care-map', [\App\Http\Controllers\Api\V1\CareMapController::class, 'publicDirectory'])->name('public.care-map');
Route::get('/care-map/facility/{id}', [\App\Http\Controllers\Api\V1\CareMapController::class, 'publicProfile'])->name('public.care-map.profile');
Route::get('/care-map/emergency', [\App\Http\Controllers\Api\V1\CareMapController::class, 'publicEmergency'])->name('public.care-map.emergency');
Route::get('/admin/care-map/governance', [\App\Http\Controllers\Api\V1\CareMapController::class, 'adminGovernance'])->name('admin.care-map.governance');



