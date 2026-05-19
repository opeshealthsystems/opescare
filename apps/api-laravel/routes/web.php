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

    // ── Staff: Visit Flow ────────────────────────────────────────
    Route::get('/portals/staff/visits', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'index'])->name('portals.staff.visits');
    Route::post('/portals/staff/visits', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'store'])->name('portals.staff.visits.store');
    Route::post('/portals/staff/visits/{id}/transition', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'transition'])->name('portals.staff.visits.transition');
    Route::post('/portals/staff/visits/{id}/complete', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'complete'])->name('portals.staff.visits.complete');
    Route::post('/portals/staff/visits/{id}/cancel', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'cancel'])->name('portals.staff.visits.cancel');
    Route::get('/portals/staff/visits/{id}/triage', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'triage'])->name('portals.staff.visits.triage');
    Route::post('/portals/staff/visits/{id}/triage', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'triageStore'])->name('portals.staff.visits.triage.store');
    Route::post('/portals/staff/visits/{id}/triage/escalate', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'triageEscalate'])->name('portals.staff.visits.triage.escalate');
    Route::get('/portals/staff/visits/{id}/consult', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'consult'])->name('portals.staff.visits.consult');
    Route::post('/portals/staff/visits/{id}/consult', [\App\Http\Controllers\MedicalId\VisitPortalController::class, 'consultStore'])->name('portals.staff.visits.consult.store');

    // --- Analytics Dashboard ---
    Route::get('/portals/staff/analytics', [\App\Http\Controllers\MedicalId\AnalyticsDashboardController::class, 'index'])->name('portals.staff.analytics');

    // --- Inventory Portal ---
    Route::get('/portals/staff/inventory/pharmacy', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacy'])->name('portals.staff.inventory.pharmacy');
    Route::post('/portals/staff/inventory/pharmacy', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacyStore'])->name('portals.staff.inventory.pharmacy.store');
    Route::post('/portals/staff/inventory/pharmacy/{id}/restock', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacyRestock'])->name('portals.staff.inventory.pharmacy.restock');
    Route::post('/portals/staff/inventory/pharmacy/{id}/dispense', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacyDispense'])->name('portals.staff.inventory.pharmacy.dispense');
    Route::post('/portals/staff/inventory/pharmacy/{id}/flag', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacyFlag'])->name('portals.staff.inventory.pharmacy.flag');
    Route::delete('/portals/staff/inventory/pharmacy/{id}', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'pharmacyDelete'])->name('portals.staff.inventory.pharmacy.delete');
    Route::get('/portals/staff/inventory/blood', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'blood'])->name('portals.staff.inventory.blood');
    Route::post('/portals/staff/inventory/blood', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'bloodUpsert'])->name('portals.staff.inventory.blood.upsert');
    Route::post('/portals/staff/inventory/blood/{id}/adjust', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'bloodAdjust'])->name('portals.staff.inventory.blood.adjust');
    Route::post('/portals/staff/inventory/blood/{id}/flag', [\App\Http\Controllers\MedicalId\InventoryPortalController::class, 'bloodFlag'])->name('portals.staff.inventory.blood.flag');

    // --- Staff HR Portal ---
    Route::get('/portals/staff/hr/directory', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'directory'])->name('portals.staff.hr.directory');
    Route::post('/portals/staff/hr/directory', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'directoryStore'])->name('portals.staff.hr.directory.store');
    Route::post('/portals/staff/hr/directory/{id}/status', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'directoryStatus'])->name('portals.staff.hr.directory.status');
    Route::post('/portals/staff/hr/directory/{id}/license', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'addLicense'])->name('portals.staff.hr.directory.license');
    Route::get('/portals/staff/hr/shifts', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'shifts'])->name('portals.staff.hr.shifts');
    Route::post('/portals/staff/hr/shifts', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'shiftsStore'])->name('portals.staff.hr.shifts.store');
    Route::post('/portals/staff/hr/shifts/{id}/toggle', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'shiftsToggle'])->name('portals.staff.hr.shifts.toggle');
    Route::get('/portals/staff/hr/roster', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'roster'])->name('portals.staff.hr.roster');
    Route::post('/portals/staff/hr/roster', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'rosterStore'])->name('portals.staff.hr.roster.store');
    Route::post('/portals/staff/hr/roster/{id}/publish', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'rosterPublish'])->name('portals.staff.hr.roster.publish');
    Route::post('/portals/staff/hr/roster/{id}/archive', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'rosterArchive'])->name('portals.staff.hr.roster.archive');
    Route::post('/portals/staff/hr/roster/{rosterId}/assign', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'rosterAssign'])->name('portals.staff.hr.roster.assign');
    Route::delete('/portals/staff/hr/roster/assignment/{assignmentId}', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'rosterUnassign'])->name('portals.staff.hr.roster.unassign');
    Route::get('/portals/staff/hr/leave', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'leave'])->name('portals.staff.hr.leave');
    Route::post('/portals/staff/hr/leave', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'leaveStore'])->name('portals.staff.hr.leave.store');
    Route::post('/portals/staff/hr/leave/{id}/approve', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'leaveApprove'])->name('portals.staff.hr.leave.approve');
    Route::post('/portals/staff/hr/leave/{id}/reject', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'leaveReject'])->name('portals.staff.hr.leave.reject');
    Route::post('/portals/staff/hr/leave/{id}/withdraw', [\App\Http\Controllers\MedicalId\StaffHRPortalController::class, 'leaveWithdraw'])->name('portals.staff.hr.leave.withdraw');

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
    Route::post('/portals/staff/support/{id}/escalate', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'supportEscalate'])->name('portals.staff.support.escalate');
    Route::post('/portals/staff/support/{id}/assign', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'supportAssign'])->name('portals.staff.support.assign');

    // ── Data Import Portal ────────────────────────────────────────
    Route::get('/portals/staff/data-import',                    [\App\Http\Controllers\MedicalId\DataImportController::class, 'index'])->name('portals.staff.data_import.index');
    Route::get('/portals/staff/data-import/upload',             [\App\Http\Controllers\MedicalId\DataImportController::class, 'create'])->name('portals.staff.data_import.create');
    Route::post('/portals/staff/data-import',                   [\App\Http\Controllers\MedicalId\DataImportController::class, 'store'])->name('portals.staff.data_import.store');
    Route::get('/portals/staff/data-import/{id}/mapping',       [\App\Http\Controllers\MedicalId\DataImportController::class, 'mapping'])->name('portals.staff.data_import.mapping');
    Route::post('/portals/staff/data-import/{id}/mapping',      [\App\Http\Controllers\MedicalId\DataImportController::class, 'mappingStore'])->name('portals.staff.data_import.mapping.store');
    Route::post('/portals/staff/data-import/{id}/validate',     [\App\Http\Controllers\MedicalId\DataImportController::class, 'validate'])->name('portals.staff.data_import.validate');
    Route::get('/portals/staff/data-import/{id}/preview',       [\App\Http\Controllers\MedicalId\DataImportController::class, 'preview'])->name('portals.staff.data_import.preview');
    Route::post('/portals/staff/data-import/{id}/approve',      [\App\Http\Controllers\MedicalId\DataImportController::class, 'approve'])->name('portals.staff.data_import.approve');
    Route::post('/portals/staff/data-import/{id}/rollback',     [\App\Http\Controllers\MedicalId\DataImportController::class, 'rollback'])->name('portals.staff.data_import.rollback');
    Route::post('/portals/staff/data-import/{id}/cancel',       [\App\Http\Controllers\MedicalId\DataImportController::class, 'cancel'])->name('portals.staff.data_import.cancel');
    Route::get('/portals/staff/data-import/{id}/audit',         [\App\Http\Controllers\MedicalId\DataImportController::class, 'auditLog'])->name('portals.staff.data_import.audit');

    // --- Global Search ---
    Route::get('/portals/staff/search', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'search'])->name('portals.staff.search');

    // --- Ward / Admission / Bed Management ---
    Route::get('/portals/staff/wards',                            [\App\Http\Controllers\MedicalId\WardController::class, 'index'])->name('portals.staff.wards');
    Route::post('/portals/staff/wards',                           [\App\Http\Controllers\MedicalId\WardController::class, 'wardStore'])->name('portals.staff.wards.store');
    Route::get('/portals/staff/wards/admissions',                 [\App\Http\Controllers\MedicalId\WardController::class, 'admissions'])->name('portals.staff.wards.admissions');
    Route::post('/portals/staff/wards/admissions',                [\App\Http\Controllers\MedicalId\WardController::class, 'admitStore'])->name('portals.staff.wards.admit');
    Route::post('/portals/staff/wards/admissions/{id}/discharge', [\App\Http\Controllers\MedicalId\WardController::class, 'dischargeStore'])->name('portals.staff.wards.discharge');
    Route::post('/portals/staff/wards/admissions/{id}/transfer',  [\App\Http\Controllers\MedicalId\WardController::class, 'transferStore'])->name('portals.staff.wards.transfer');

    // --- File Storage & Medical Attachments ---
    Route::get('/portals/staff/files',              [\App\Http\Controllers\MedicalId\FileStorageController::class, 'index'])->name('portals.staff.files.index');
    Route::get('/portals/staff/files/upload',       [\App\Http\Controllers\MedicalId\FileStorageController::class, 'create'])->name('portals.staff.files.create');
    Route::post('/portals/staff/files',             [\App\Http\Controllers\MedicalId\FileStorageController::class, 'store'])->name('portals.staff.files.store');
    Route::get('/portals/staff/files/{id}/download',[\App\Http\Controllers\MedicalId\FileStorageController::class, 'download'])->name('portals.staff.files.download');
    Route::delete('/portals/staff/files/{id}',      [\App\Http\Controllers\MedicalId\FileStorageController::class, 'destroy'])->name('portals.staff.files.destroy');

    // --- Supply Chain Portal ---
    Route::get('/portals/staff/supply',                                     [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'index'])->name('portals.staff.supply');
    Route::get('/portals/staff/supply/items',                               [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'items'])->name('portals.staff.supply.items');
    Route::post('/portals/staff/supply/items',                              [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'itemStore'])->name('portals.staff.supply.items.store');
    Route::get('/portals/staff/supply/suppliers',                           [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'suppliers'])->name('portals.staff.supply.suppliers');
    Route::post('/portals/staff/supply/suppliers',                          [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'supplierStore'])->name('portals.staff.supply.suppliers.store');
    Route::get('/portals/staff/supply/stock',                               [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'stock'])->name('portals.staff.supply.stock');
    Route::post('/portals/staff/supply/stock/receive',                      [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'stockReceive'])->name('portals.staff.supply.stock.receive');
    Route::post('/portals/staff/supply/stock/{id}/adjust',                  [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'stockAdjust'])->name('portals.staff.supply.stock.adjust');
    Route::get('/portals/staff/supply/purchase-orders',                     [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'purchaseOrders'])->name('portals.staff.supply.purchase_orders');
    Route::post('/portals/staff/supply/purchase-orders',                    [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'purchaseOrderStore'])->name('portals.staff.supply.purchase_orders.store');
    Route::post('/portals/staff/supply/purchase-orders/{id}/approve',       [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'purchaseOrderApprove'])->name('portals.staff.supply.purchase_orders.approve');
    Route::get('/portals/staff/supply/goods-receipts',                      [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'goodsReceipts'])->name('portals.staff.supply.goods_receipts');
    Route::post('/portals/staff/supply/goods-receipts',                     [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'goodsReceiptsStore'])->name('portals.staff.supply.goods_receipts.store');
    Route::get('/portals/staff/supply/movements',                           [\App\Http\Controllers\MedicalId\SupplyChainController::class, 'movements'])->name('portals.staff.supply.movements');

    // --- Insurance Portal ---
    Route::get('/portals/insurance/providers', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'providers'])->name('portals.insurance.providers');
    Route::post('/portals/insurance/providers', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'providersStore'])->name('portals.insurance.providers.store');
    Route::post('/portals/insurance/providers/{providerId}/plans', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'plansStore'])->name('portals.insurance.plans.store');

    Route::get('/portals/insurance/policies', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'policies'])->name('portals.insurance.policies');
    Route::post('/portals/insurance/policies', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'policiesStore'])->name('portals.insurance.policies.store');
    Route::post('/portals/insurance/policies/{id}/activate', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'policiesActivate'])->name('portals.insurance.policies.activate');
    Route::post('/portals/insurance/policies/{id}/deactivate', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'policiesDeactivate'])->name('portals.insurance.policies.deactivate');
    Route::post('/portals/insurance/policies/{policyId}/eligibility', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'eligibilityStore'])->name('portals.insurance.eligibility.store');

    Route::get('/portals/insurance/preauths', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'preauths'])->name('portals.insurance.preauths');
    Route::post('/portals/insurance/preauths', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'preauthsStore'])->name('portals.insurance.preauths.store');
    Route::post('/portals/insurance/preauths/{id}/submit', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'preauthsSubmit'])->name('portals.insurance.preauths.submit');
    Route::post('/portals/insurance/preauths/{id}/decide', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'preauthsDecide'])->name('portals.insurance.preauths.decide');
    Route::post('/portals/insurance/preauths/{id}/cancel', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'preauthsCancel'])->name('portals.insurance.preauths.cancel');

    Route::get('/portals/insurance/claims', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claims'])->name('portals.insurance.claims');
    Route::post('/portals/insurance/claims', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claimsStore'])->name('portals.insurance.claims.store');
    Route::post('/portals/insurance/claims/{id}/submit', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claimsSubmit'])->name('portals.insurance.claims.submit');
    Route::post('/portals/insurance/claims/{id}/decide', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claimsDecide'])->name('portals.insurance.claims.decide');
    Route::post('/portals/insurance/claims/{id}/cancel', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claimsCancel'])->name('portals.insurance.claims.cancel');
    Route::post('/portals/insurance/claims/{id}/pay', [\App\Http\Controllers\MedicalId\InsurancePortalController::class, 'claimsPay'])->name('portals.insurance.claims.pay');

    Route::get('/portals/admin', [\App\Http\Controllers\MedicalId\AdminPortalController::class, 'index'])->name('portals.admin');
    Route::get('/portals/admin/go-live', [\App\Http\Controllers\Api\V1\Admin\FacilityGoLiveReadinessController::class, 'index'])->name('portals.admin.go-live');

    // ── Master Admin Control Center ───────────────────────────────
    Route::get('/portals/admin/cc',                      [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'index'])->name('portals.admin.cc');
    Route::get('/portals/admin/cc/settings',             [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'settings'])->name('portals.admin.cc.settings');
    Route::post('/portals/admin/cc/settings',            [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'settingsUpdate'])->name('portals.admin.cc.settings.update');
    Route::get('/portals/admin/cc/feature-flags',        [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'featureFlags'])->name('portals.admin.cc.feature_flags');
    Route::post('/portals/admin/cc/feature-flags/{key}', [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'featureFlagToggle'])->name('portals.admin.cc.feature_flags.toggle');
    Route::get('/portals/admin/cc/modules',              [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'modules'])->name('portals.admin.cc.modules');
    Route::post('/portals/admin/cc/modules/{key}',       [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'moduleToggle'])->name('portals.admin.cc.modules.toggle');
    Route::get('/portals/admin/cc/maintenance',          [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'maintenance'])->name('portals.admin.cc.maintenance');
    Route::post('/portals/admin/cc/maintenance',         [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'maintenanceStore'])->name('portals.admin.cc.maintenance.store');
    Route::post('/portals/admin/cc/maintenance/{id}',    [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'maintenanceToggle'])->name('portals.admin.cc.maintenance.toggle');
    Route::get('/portals/admin/cc/health',               [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'systemHealth'])->name('portals.admin.cc.health');
    Route::get('/portals/admin/cc/audit',                [\App\Http\Controllers\MedicalId\AdminControlCenterController::class, 'auditLog'])->name('portals.admin.cc.audit');

    // --- Connect Suite Admin Portal ---
    Route::get('/portals/admin/connect',                       [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'index'])->name('portals.admin.connect');
    Route::get('/portals/admin/connect/clients',               [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'clients'])->name('portals.admin.connect.clients');
    Route::post('/portals/admin/connect/clients',              [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'clientStore'])->name('portals.admin.connect.clients.store');
    Route::post('/portals/admin/connect/clients/{id}/action',  [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'clientAction'])->name('portals.admin.connect.clients.action');
    Route::get('/portals/admin/connect/tokens',                [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'tokens'])->name('portals.admin.connect.tokens');
    Route::post('/portals/admin/connect/tokens',               [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'tokenStore'])->name('portals.admin.connect.tokens.store');
    Route::post('/portals/admin/connect/tokens/{id}/revoke',   [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'tokenRevoke'])->name('portals.admin.connect.tokens.revoke');
    Route::get('/portals/admin/connect/webhooks',              [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'webhooks'])->name('portals.admin.connect.webhooks');
    Route::post('/portals/admin/connect/webhooks/{id}/toggle', [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'webhookToggle'])->name('portals.admin.connect.webhooks.toggle');
    Route::get('/portals/admin/connect/widget',                [\App\Http\Controllers\MedicalId\ConnectPortalController::class, 'widget'])->name('portals.admin.connect.widget');

    // --- Security Operations Center ---
    Route::get('/portals/admin/security',                    [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'index'])->name('portals.admin.security');
    Route::get('/portals/admin/security/incidents',          [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'incidents'])->name('portals.admin.security.incidents');
    Route::post('/portals/admin/security/incidents',         [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'incidentStore'])->name('portals.admin.security.incidents.store');
    Route::post('/portals/admin/security/incidents/{id}',    [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'incidentUpdate'])->name('portals.admin.security.incidents.update');
    Route::get('/portals/admin/security/emergency-access',   [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'emergencyAccess'])->name('portals.admin.security.emergency_access');
    Route::get('/portals/admin/security/audit-explorer',     [\App\Http\Controllers\MedicalId\SecurityOpsController::class, 'auditExplorer'])->name('portals.admin.security.audit_explorer');
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



