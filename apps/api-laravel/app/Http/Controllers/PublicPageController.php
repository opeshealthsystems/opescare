<?php

namespace App\Http\Controllers;

use App\Mail\OpesCareNotificationMail;
use App\Models\Facility;
use App\Models\Lead;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Modules\Auth\Services\TwoFactorService;
use App\Services\Dashboard\DashboardProfileService;
use App\Services\Identity\HealthIdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicPageController extends Controller
{
    public function index()
    {
        return view('public.landing');
    }

    /*
     * Network services — the destination pages for the medicine and blood
     * availability detail that used to occupy two full sections of the
     * homepage. They are network capabilities, not the definition of OpesCare,
     * so the homepage carries a card each and the substance lives here.
     */
    public function networkMedicineFinder()
    {
        return view('public.network.medicine_finder');
    }

    public function networkBloodFinder()
    {
        return view('public.network.blood_finder');
    }

    /**
     * Shown to a signed-in user whose portal is frozen out of this release.
     * Reached only by redirect from EnsurePortalAccess — the frozen URL
     * itself still 404s, unchanged.
     */
    public function showPortalUnavailable()
    {
        return view('auth.portal_unavailable');
    }

    public function about()
    {
        return view('public.about');
    }

    public function howItWorks()
    {
        return view('public.how_it_works');
    }

    public function solutionsPatients()
    {
        return view('public.solutions.patients');
    }

    public function solutionsHospitals()
    {
        return view('public.solutions.hospitals');
    }

    public function solutionsPharmacies()
    {
        return view('public.solutions.pharmacies');
    }

    public function solutionsLaboratories()
    {
        return view('public.solutions.laboratories');
    }

    public function solutionsInsurers()
    {
        return view('public.solutions.insurers');
    }

    public function solutionsPublicHealth()
    {
        return view('public.solutions.public_health');
    }

    public function interoperability()
    {
        return view('public.interoperability');
    }

    public function developers()
    {
        return view('public.developers');
    }

    public function security()
    {
        return view('public.security');
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }

    public function consent()
    {
        return view('public.consent');
    }

    public function faq()
    {
        return view('public.faq');
    }

    public function help()
    {
        return view('public.help');
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function contactSubmit(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'name'              => 'required|string|max:120',
            'email'             => 'required|email|max:180',
            'subject'           => 'nullable|string|max:60',
            'message'           => 'required|string|max:5000',
            // Partner inquiry extras (from landing page form)
            'organisation'      => 'nullable|string|max:160',
            'organization'      => 'nullable|string|max:160',
            'role'              => 'nullable|string|max:100',
            'phone'             => 'nullable|string|max:30',
            'organization_type' => 'nullable|string|max:60',
            'country'           => 'nullable|string|max:80',
        ]);

        $supportEmail = config('mail.support_address', config('mail.from.address'));
        $name    = $request->input('name');
        $email   = $request->input('email');
        $subject = $request->input('subject', 'Contact enquiry');
        $org     = $request->input('organisation') ?? $request->input('organization', '');
        $body    = "From: {$name} <{$email}>" . ($org ? "\nOrganisation: {$org}" : '') . "\n\n" . $request->input('message');

        /*
         * Record the enquiry BEFORE sending anything.
         *
         * This used to queue an email and nothing else, to an address on a
         * domain that accepts no inbound mail — so every message a visitor
         * sent through the contact form was discarded while the page thanked
         * them for it. The lead is the record; the email is a notification.
         */
        Lead::create([
            'name'              => $name,
            'email'             => $email,
            'phone'             => $request->input('phone'),
            'organization_name' => $org ?: null,
            'organization_type' => in_array($request->input('organization_type'), Lead::ORGANIZATION_TYPES, true)
                ? $request->input('organization_type')
                : 'other',
            'message'           => trim(($subject ? "Subject: {$subject}
" : '') . $body),
            'source'            => 'contact',
            'status'            => 'new',
        ]);

        // A mail transport failure must not lose an enquiry already recorded.
        try {
            Mail::to($supportEmail)->queue(new OpesCareNotificationMail(
                mailSubject: "OpesCare Contact: {$subject}",
                bodyText: $body,
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('contact_enquiry_mail_failed', [
                'error' => $e->getMessage(),
            ]);
        }

        // Redirect back to the originating page with success flag
        $back = url()->previous();
        return redirect($back)->with('contact_success', true)->with('success', __('flash.contact_message_received'));
    }

    public function status(\App\Modules\Admin\Services\SystemHealthService $health)
    {
        // Active or upcoming maintenance windows are the public incident feed —
        // never expose internal SecurityIncident records here.
        $maintenance = \App\Models\MaintenanceWindow::query()
            ->where('is_active', true)
            ->orWhere('starts_at', '>=', now())
            ->orderByDesc('starts_at')
            ->limit(5)
            ->get();

        return view('public.status', [
            'health'      => $health->currentHealth(),
            'maintenance' => $maintenance,
        ]);
    }

    public function sla()
    {
        return view('public.sla');
    }

    public function pricing()
    {
        return view('public.api-pricing', ['plans' => \App\Models\ApiPlan::public()]);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function submitLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        // Attempt real authentication
        if (!Auth::attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('onboarding.login.errors.invalid_credentials', [], app()->getLocale())
                    ?: 'The email or password you entered is incorrect.']);
        }

        $user = Auth::user();

        // Suspended account
        if ($user->status === 'suspended') {
            Auth::logout();
            return redirect()->route('account.suspended');
        }

        // Pending account
        if ($user->status === 'pending') {
            Auth::logout();
            return redirect()->route('account.pending');
        }

        if ($user->requiresTwoFactor()) {
            Auth::logout();
            $request->session()->regenerate();
            $request->session()->put('mfa.user_id', $user->id);
            $request->session()->put('mfa.remember', $remember);
            $request->session()->put('mfa.setup_required', ! $user->hasTwoFactorEnabled());

            return redirect()->route('mfa.challenge');
        }

        $request->session()->regenerate();
        $request->session()->put('mfa.verified', true);

        // Route to correct portal based on role
        $landingUrl = app(DashboardProfileService::class)->landingUrlForUser($user);

        return redirect()->intended($landingUrl);
    }

    public function showMfaChallenge(Request $request)
    {
        if (! $request->session()->has('mfa.user_id')) {
            return redirect()->route('login');
        }

        return view('auth.mfa_challenge', [
            'setupRequired' => (bool) $request->session()->get('mfa.setup_required', false),
        ]);
    }

    public function submitMfaChallenge(Request $request, TwoFactorService $twoFactor)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20',
        ]);

        $user = User::find($request->session()->get('mfa.user_id'));

        if (! $user) {
            $request->session()->forget(['mfa.user_id', 'mfa.remember', 'mfa.setup_required']);

            return redirect()->route('login')->with('error', __('flash.secure_session_expired'));
        }

        if (! $user->hasTwoFactorEnabled()) {
            return back()->with('error', __('flash.mfa_setup_required'));
        }

        if (! $twoFactor->verify($user->two_factor_secret, $validated['code'])) {
            return back()->withErrors(['code' => __('flash.auth_code_invalid')]);
        }

        Auth::login($user, (bool) $request->session()->get('mfa.remember', false));
        $request->session()->regenerate();
        $request->session()->put('mfa.verified', true);
        $request->session()->forget(['mfa.user_id', 'mfa.remember', 'mfa.setup_required']);

        $landingUrl = app(DashboardProfileService::class)->landingUrlForUser($user);

        return redirect()->intended($landingUrl);
    }

    public function showRegisterSelector()
    {
        return view('auth.register');
    }

    public function showPatientRegister()
    {
        return view('auth.register.patient');
    }

    /**
     * Patient sign-up: email and password, nothing else.
     *
     * It used to demand nine fields — names, date of birth, sex, phone and a
     * full emergency contact — before anyone could create an account. Everything
     * except the credentials now moves to profile completion, after login.
     *
     * No Patient row and no Health ID are created here, deliberately. A Health
     * ID is a *verified identity*; minting one against a blank record would put
     * unidentifiable rows into the Master Patient Index that later have to be
     * reconciled, which is exactly what the no-auto-merge invariant exists to
     * avoid. Identity is created once there is an identity to bind it to.
     *
     * The account is also created ACTIVE. It used to be 'pending', while
     * submitLogin() rejects 'pending' and redirects to the approval screen — so
     * every self-registered patient was locked out of the account they had just
     * made. Patients are self-service; there is nobody to approve them.
     */
    public function submitPatientRegister(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|max:180|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => __('onboarding.patient.email_taken'),
        ]);

        $user = DB::transaction(function () use ($data) {
            $role = Role::where('name', 'patient')->first();

            $user = User::create([
                // A display name is NOT NULL on users; the local-part is a
                // placeholder replaced by the real name at profile completion.
                'name'     => Str::before($data['email'], '@'),
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'status'   => 'active',
            ]);

            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            }

            return $user;
        });

        // Referral capture must never break sign-up.
        $refCode = trim((string) $request->input('ref', ''));
        if ($refCode !== '') {
            $request->session()->put('pending_referral_code', $refCode);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('mfa.verified', true);

        return redirect()->route('portals.patient.complete-profile')
            ->with('success', __('onboarding.patient.account_created'));
    }

    public function showGuardianRegister()
    {
        return view('auth.register.guardian');
    }

    /**
     * Caregiver sign-up — an email and a password, like every other account.
     *
     * What used to be one 16-field form is now two steps. Guardian access to
     * somebody else's record still requires institutional verification before
     * it becomes active, which is what the success message has always said;
     * the identity and dependant details that a reviewer needs are collected
     * after login, at /portals/guardian/complete-profile.
     */
    public function submitGuardianRegister(Request $request)
    {
        $data = $request->validate([
            'email'    => 'required|email|max:180|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'email.unique' => __('onboarding.guardian.email_taken'),
        ]);

        $user = DB::transaction(function () use ($data) {
            $role = Role::where('name', 'guardian')->first();

            $user = User::create([
                'name'     => Str::before($data['email'], '@'),
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
                'status'   => 'active',
            ]);

            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('mfa.verified', true);

        return redirect()->route('portals.guardian.complete-profile')
            ->with('success', __('onboarding.guardian.account_created'));
    }


    public function showOrganizationRegister()
    {
        return view('auth.register.organization');
    }

    public function submitOrganizationRegister(Request $request)
    {
        $data = $request->validate([
            'org_type'      => 'required|string|max:60',
            'legal_name'    => 'required|string|max:200',
            'trade_name'    => 'nullable|string|max:200',
            'reg_number'    => 'required|string|max:80',
            'license_number'=> 'required|string|max:80',
            'address'       => 'required|string|max:300',
            'main_phone'    => 'required|string|max:30',
            'main_email'    => 'required|email|max:180',
            'contact_name'  => 'required|string|max:120',
            'contact_role'  => 'required|string|max:100',
            'contact_email' => 'required|email|max:180',
            'contact_phone' => 'required|string|max:30',
        ]);

        $refCode  = 'OPC-' . strtoupper(bin2hex(random_bytes(4)));
        $adminEmail = config('mail.support_address', config('mail.from.address'));

        $body = "New Organisation Application\n\n"
            . "Ref: {$refCode}\n"
            . "Type: {$data['org_type']}\n"
            . "Legal Name: {$data['legal_name']}\n"
            . "Reg#: {$data['reg_number']} | License#: {$data['license_number']}\n"
            . "Address: {$data['address']}\n"
            . "Main: {$data['main_email']} / {$data['main_phone']}\n"
            . "Contact: {$data['contact_name']} ({$data['contact_role']}) — {$data['contact_email']} / {$data['contact_phone']}\n";

        /*
         * Record the application BEFORE sending anything.
         *
         * The email used to be its only trace. Production has no SMTP host
         * configured, so a queued mail that never leaves took the whole
         * application with it — and the reference code below was handed to the
         * applicant for a record that existed nowhere. The lead IS the record;
         * the email is only a notification about it.
         */
        Lead::create([
            'name'              => $data['contact_name'],
            'email'             => $data['contact_email'],
            'phone'             => $data['contact_phone'],
            'organization_name' => $data['legal_name'],
            'organization_type' => in_array($data['org_type'], Lead::ORGANIZATION_TYPES, true)
                ? $data['org_type']
                : 'other',
            'message'           => $body,
            'source'            => 'organization_application',
            'status'            => 'new',
        ]);

        // A mail transport failure must not lose an application that is
        // already safely recorded.
        try {
            Mail::to($adminEmail)->queue(new OpesCareNotificationMail(
                mailSubject: "OpesCare Organisation Application: {$data['legal_name']} [{$refCode}]",
                bodyText: $body,
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('organisation_application_mail_failed', [
                'ref'   => $refCode,
                'error' => $e->getMessage(),
            ]);
        }

        return view('auth.register.organization', [
            'success_application' => true,
            'ref_code'   => $refCode,
            'legal_name' => $data['legal_name'],
        ]);
    }

    public function showDeveloperRegister()
    {
        return view('auth.register.developer');
    }

    /**
     * Connect API access request — reviewed by the interoperability panel,
     * exactly as the success message says. It used to say so and record
     * nothing, so no request ever reached the panel.
     */
    public function submitDeveloperRegister(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:120',
            'email'               => 'required|email|max:180',
            'phone'               => 'nullable|string|max:30',
            'organization'        => 'required|string|max:200',
            'role'                => 'nullable|string|max:100',
            'country'             => 'nullable|string|max:80',
            'system_type'         => 'nullable|string|max:120',
            'integration_purpose' => 'nullable|string|max:2000',
            'data_flow'           => 'nullable|string|max:60',
            'sandbox'             => 'nullable',
            'production'          => 'nullable',
        ]);

        $lines = [];
        foreach ([
            'Role'         => $data['role'] ?? null,
            'Country'      => $data['country'] ?? null,
            'System type'  => $data['system_type'] ?? null,
            'Data flow'    => $data['data_flow'] ?? null,
            'Sandbox'      => $request->boolean('sandbox') ? 'yes' : null,
            'Production'   => $request->boolean('production') ? 'yes' : null,
        ] as $label => $value) {
            if (! empty($value)) {
                $lines[] = $label . ': ' . $value;
            }
        }
        if (! empty($data['integration_purpose'])) {
            $lines[] = '';
            $lines[] = 'Integration purpose: ' . $data['integration_purpose'];
        }

        Lead::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'organization_name' => $data['organization'],
            'organization_type' => 'developer',
            'message'           => implode("
", $lines),
            'source'            => 'developer_signup',
            'status'            => 'new',
        ]);

        return redirect()->route('register.developer')->with('success', __('flash.developer_request_submitted'));
    }

    public function showStaffInvite($token)
    {
        if ($token === 'expired') {
            return view('auth.invite', ['error' => 'expired']);
        }
        if ($token === 'used') {
            return view('auth.invite', ['error' => 'used']);
        }
        if ($token === 'revoked') {
            return view('auth.invite', ['error' => 'revoked']);
        }

        return view('auth.invite', [
            'token' => $token,
            'org_name' => 'Metro Clinical Diagnostics Lab',
            'facility_name' => 'Down-Town Collection Center Branch',
            'role_name' => 'Senior Laboratory Technologist',
            'invited_by' => 'Dr. Elizabeth Blackwell',
            'expiry' => now()->addDays(3)->format('Y-m-d H:i')
        ]);
    }

    public function submitStaffInvite(Request $request, $token)
    {
        return redirect()->route('login')->with('success', __('flash.staff_account_activated'));
    }

    public function showForgotPassword()
    {
        return view('auth.forgot_password');
    }

    public function submitForgotPassword(Request $request)
    {
        return redirect()->route('password.request')->with('success', __('onboarding.forgot.success'));
    }

    public function showResetPassword($token)
    {
        return view('auth.reset_password', ['token' => $token]);
    }

    public function submitResetPassword(Request $request, $token)
    {
        return redirect()->route('login')->with('success', __('onboarding.forgot.reset_success'));
    }

    public function showVerifyOtp()
    {
        return view('auth.verify_otp');
    }

    public function submitVerifyOtp(Request $request)
    {
        $code = implode('', $request->input('otp', []));
        if ($code === '000000') {
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.incorrect'));
        }
        if ($code === '111111') {
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.expired'));
        }

        $url = Auth::check()
            ? app(DashboardProfileService::class)->landingUrlForCurrent()
            : route('portals.patient');

        return redirect($url)->with('success', __('flash.authentication_complete'));
    }

    public function resendOtp(Request $request)
    {
        return redirect()
            ->route('otp.verify')
            ->with('success', __('flash.otp_resent'));
    }

    public function showPendingApproval()
    {
        return view('auth.pending_approval', [
            'ref_code' => 'OPC-' . strtoupper(bin2hex(random_bytes(4))),
            'org_name' => 'St. Mary Pediatric Care Clinic',
            'submitted_date' => now()->subDays(2)->format('Y-m-d H:i')
        ]);
    }

    public function showAccountSuspended()
    {
        return view('auth.account_suspended');
    }

    public function showSelectFacility()
    {
        $user     = Auth::user();
        $roleName = $user?->role?->description ?? $user?->role?->name ?? 'User';

        // Build a list of selectable facilities.
        // Platform-level users (no primary facility) see all active facilities.
        // A user with a primary facility would normally never reach this page,
        // but we handle it gracefully by showing their own facility.
        $query = Facility::withoutGlobalScope('isolate_demo')
            ->orderBy('name');

        if ($user?->primary_facility_id) {
            $query->where('id', $user->primary_facility_id);
        }

        $facilities = $query->get()->map(fn(Facility $f) => [
            'id'     => $f->id,
            'name'   => $f->name,
            'branch' => ucfirst($f->type ?? ''),
            'role'   => $roleName,
            'status' => ($f->status === 'suspended') ? 'suspended' : 'active',
        ])->values()->all();

        return view('auth.select_facility', compact('facilities'));
    }

    public function submitSelectFacility(Request $request)
    {
        $facilityId = $request->input('facility');

        if ($facilityId === 'suspended') {
            return redirect()->route('select-facility')
                ->with('error', __('onboarding.login.errors.facility_suspended'));
        }

        if (!$facilityId) {
            return redirect()->route('select-facility')
                ->with('error', __('flash.facility_select_required'));
        }

        // ✅ Save the chosen facility to session so RequireFacilityContext passes
        session(['active_facility_id' => $facilityId]);

        $url = Auth::check()
            ? app(DashboardProfileService::class)->landingUrlForCurrent()
            : route('login');

        return redirect($url)->with('success', __('flash.clinical_session_established'));
    }
}
