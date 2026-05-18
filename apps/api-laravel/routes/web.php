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
    Route::get('/portals/patient/appointments', function (\Illuminate\Http\Request $request) {
        $appointments = \App\Models\Appointment::query()
            ->when($request->query('patient_id'), fn ($query, $patientId) => $query->where('patient_id', $patientId))
            ->orderBy('scheduled_at')
            ->get();

        return view('portals.patient.appointments', ['appointments' => $appointments]);
    })->name('portals.patient.appointments');

    Route::get('/portals/staff', [\App\Http\Controllers\MedicalId\StaffPortalController::class, 'index'])->name('portals.staff');
    Route::get('/portals/staff/appointments', function (\Illuminate\Http\Request $request) {
        $appointments = \App\Models\Appointment::query()
            ->when($request->query('facility_id'), fn ($query, $facilityId) => $query->where('facility_id', $facilityId))
            ->when($request->query('provider_id'), fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->orderBy('scheduled_at')
            ->get();

        return view('portals.staff.appointments', ['appointments' => $appointments]);
    })->name('portals.staff.appointments');
    Route::get('/portals/staff/queue', function (\Illuminate\Http\Request $request) {
        $tickets = \App\Models\QueueTicket::query()
            ->with('patient')
            ->when($request->query('facility_id'), fn ($query, $facilityId) => $query->where('facility_id', $facilityId))
            ->when($request->query('queue_name'), fn ($query, $queueName) => $query->where('current_queue', $queueName))
            ->orderBy('priority_level')
            ->orderBy('checked_in_at')
            ->get();

        return view('portals.staff.queue', ['tickets' => $tickets]);
    })->name('portals.staff.queue');
    Route::get('/portals/staff/billing', function (\Illuminate\Http\Request $request) {
        $invoices = \App\Models\Invoice::query()
            ->when($request->query('facility_id'), fn ($query, $facilityId) => $query->where('facility_id', $facilityId))
            ->when($request->query('patient_id'), fn ($query, $patientId) => $query->where('patient_id', $patientId))
            ->orderByDesc('issued_at')
            ->get();

        return view('portals.staff.billing', ['invoices' => $invoices]);
    })->name('portals.staff.billing');
    Route::get('/queue-display', function (\Illuminate\Http\Request $request, \App\Modules\Queue\Services\QueueService $service) {
        $tickets = $request->query('facility_id')
            ? $service->maskedDisplay($request->query('facility_id'), $request->query('queue_name'))
            : collect();

        return view('portals.staff.queue_display', ['tickets' => $tickets]);
    })->name('queue.display');
    
    Route::get('/portals/admin', [\App\Http\Controllers\MedicalId\AdminPortalController::class, 'index'])->name('portals.admin');
    Route::get('/portals/admin/facilities/{facility}/go-live-readiness', function (
        \App\Models\Facility $facility,
        \App\Modules\FacilityReadiness\Services\FacilityGoLiveService $service
    ) {
        $readiness = $service->getOrCreateReadiness($facility->id);

        return view('portals.admin.go_live_readiness', [
            'facility' => $facility,
            'readiness' => $readiness,
            'labels' => $service->checklistLabels(),
            'missingItems' => $service->missingItems($readiness),
        ]);
    })->name('portals.admin.go-live-readiness');
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

/*
|--------------------------------------------------------------------------
| OpesCare Referral Network Staff Portal Web Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->prefix('portals/staff/referrals')->group(function () {
    Route::get('/', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\ReferralCase::query()->with(['referringFacility', 'receivingFacility']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }
        if ($request->filled('referring_facility_id')) {
            $query->where('referring_facility_id', $request->query('referring_facility_id'));
        }
        if ($request->filled('receiving_facility_id')) {
            $query->where('receiving_facility_id', $request->query('receiving_facility_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        return view('portals.staff.referrals.index', [
            'referrals' => $query->orderByDesc('created_at')->get(),
        ]);
    })->name('portals.staff.referrals');

    Route::get('/create', function () {
        return view('portals.staff.referrals.create');
    })->name('portals.staff.referrals.create');

    Route::post('/', function (\Illuminate\Http\Request $request, \App\Modules\Referral\Services\ReferralService $service) {
        $validated = $request->validate([
            'patient_id'              => ['required', 'uuid'],
            'referring_facility_id'   => ['required', 'uuid'],
            'referring_provider_id'   => ['nullable', 'uuid'],
            'receiving_facility_id'   => ['nullable', 'uuid'],
            'receiving_specialty'     => ['nullable', 'string', 'max:120'],
            'receiving_provider_name' => ['nullable', 'string', 'max:200'],
            'urgency'                 => ['nullable', 'in:routine,urgent,emergency'],
            'reason'                  => ['required', 'string'],
            'clinical_summary'        => ['nullable', 'string'],
            'expires_at'              => ['nullable', 'date', 'after:now'],
            'created_by_id'           => ['nullable', 'uuid'],
        ]);
        $referral = $service->create($validated);
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral created as draft.');
    })->name('portals.staff.referrals.store');

    Route::get('/{referral}', function (\App\Models\ReferralCase $referral) {
        return view('portals.staff.referrals.show', [
            'referral' => $referral->load(['referringFacility', 'receivingFacility']),
        ]);
    })->name('portals.staff.referrals.show');

    Route::post('/{referral}/send', function (\Illuminate\Http\Request $request, \App\Models\ReferralCase $referral, \App\Modules\Referral\Services\ReferralService $service) {
        $service->send($referral, $request->input('actor_id'));
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral sent.');
    })->name('portals.staff.referrals.send');

    Route::post('/{referral}/accept', function (\Illuminate\Http\Request $request, \App\Models\ReferralCase $referral, \App\Modules\Referral\Services\ReferralService $service) {
        $service->accept($referral, $request->input('accepted_by_id') ?? '00000000-0000-0000-0000-000000000000');
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral accepted.');
    })->name('portals.staff.referrals.accept');

    Route::post('/{referral}/reject', function (\Illuminate\Http\Request $request, \App\Models\ReferralCase $referral, \App\Modules\Referral\Services\ReferralService $service) {
        $validated = $request->validate(['reason' => ['required', 'string']]);
        $service->reject($referral, $validated['reason']);
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral rejected.');
    })->name('portals.staff.referrals.reject');

    Route::post('/{referral}/complete', function (\Illuminate\Http\Request $request, \App\Models\ReferralCase $referral, \App\Modules\Referral\Services\ReferralService $service) {
        $service->complete($referral, $request->input('feedback'));
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral marked as completed.');
    })->name('portals.staff.referrals.complete');

    Route::post('/{referral}/cancel', function (\Illuminate\Http\Request $request, \App\Models\ReferralCase $referral, \App\Modules\Referral\Services\ReferralService $service) {
        $validated = $request->validate(['reason' => ['required', 'string']]);
        $service->cancel($referral, $validated['reason']);
        return redirect()->route('portals.staff.referrals.show', $referral->id)->with('success', 'Referral cancelled.');
    })->name('portals.staff.referrals.cancel');
});

/*
|--------------------------------------------------------------------------
| OpesCare Immunization Staff Portal Web Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['web'])->prefix('portals/staff/immunizations')->group(function () {
    Route::get('/', function (\Illuminate\Http\Request $request) {
        $records  = collect();
        $schedule = collect();

        if ($request->filled('patient_id')) {
            $history  = (new \App\Modules\Immunization\Services\ImmunizationService())->getHistory($request->query('patient_id'));
            $records  = $history['records'];
            $schedule = $history['schedule'];
        }

        return view('portals.staff.immunizations.index', compact('records', 'schedule'));
    })->name('portals.staff.immunizations');

    Route::get('/record', function (\Illuminate\Http\Request $request) {
        return view('portals.staff.immunizations.record');
    })->name('portals.staff.immunizations.record');

    Route::post('/', function (\Illuminate\Http\Request $request, \App\Modules\Immunization\Services\ImmunizationService $service) {
        $validated = $request->validate([
            'patient_id'         => ['required', 'uuid'],
            'facility_id'        => ['required', 'uuid'],
            'administered_by_id' => ['nullable', 'uuid'],
            'encounter_id'       => ['nullable', 'uuid'],
            'vaccine_code'       => ['required', 'string', 'max:50'],
            'vaccine_system'     => ['nullable', 'string', 'max:50'],
            'vaccine_name'       => ['required', 'string', 'max:200'],
            'lot_number'         => ['nullable', 'string', 'max:100'],
            'manufacturer'       => ['nullable', 'string', 'max:200'],
            'administered_at'    => ['nullable', 'date'],
            'dose_number'        => ['nullable', 'integer', 'min:1'],
            'route'              => ['nullable', 'string', 'max:50'],
            'site'               => ['nullable', 'string', 'max:100'],
            'dose_quantity'      => ['nullable', 'numeric', 'min:0'],
            'expiry_date'        => ['nullable', 'date'],
            'status'             => ['nullable', 'in:completed,not_done'],
            'not_done_reason'    => ['nullable', 'string'],
            'is_historical'      => ['nullable', 'boolean'],
        ]);
        $service->record($validated);
        return redirect()->route('portals.staff.immunizations', ['patient_id' => $validated['patient_id']])->with('success', 'Immunization recorded.');
    })->name('portals.staff.immunizations.store');
});
