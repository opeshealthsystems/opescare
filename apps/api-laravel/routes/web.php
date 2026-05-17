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

// Verification & Restriction Status Displays
Route::get('/pending-approval', [PublicPageController::class, 'showPendingApproval'])->name('account.pending');
Route::get('/account-suspended', [PublicPageController::class, 'showAccountSuspended'])->name('account.suspended');

// Multi-Facility Access Plane Selector
Route::get('/select-facility', [PublicPageController::class, 'showSelectFacility'])->name('select-facility');
Route::post('/select-facility', [PublicPageController::class, 'submitSelectFacility'])->name('select-facility.submit');

// Secure Portal Access Override
Route::get('/login', [PublicPageController::class, 'showLogin'])->name('login');
Route::post('/login', [PublicPageController::class, 'submitLogin'])->name('login.submit');

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
    Route::get('/verify/health-id', function () {
        return 'Medical ID Verification Portal (Mock)';
    })->name('verify.health-id');

    Route::get('/verify/qr/{token}', function ($token) {
        return 'QR Token Verification Portal (Mock) for token: ' . $token;
    })->name('verify.qr');
});

// Portal Placeholders
Route::middleware(['web'])->group(function () {
    Route::get('/portals/patient', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'index'])->name('portals.patient');
    Route::get('/portals/patient/logs', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'accessLogs'])->name('portals.patient.logs');
    Route::post('/portals/patient/generate-qr', [\App\Http\Controllers\MedicalId\PatientPortalController::class, 'generateTemporaryQr'])->name('portals.patient.qr');

    Route::get('/portals/staff', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'index'])->name('portals.staff');
    
    Route::get('/portals/admin', [\App\Http\Controllers\MedicalId\AdminPortalController::class, 'index'])->name('portals.admin');
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

