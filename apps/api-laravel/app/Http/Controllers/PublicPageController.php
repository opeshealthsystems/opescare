<?php

namespace App\Http\Controllers;

use App\Mail\OpesCareNotificationMail;
use App\Models\Facility;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use App\Services\Dashboard\DashboardProfileService;
use App\Services\Identity\HealthIdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class PublicPageController extends Controller
{
    public function index()
    {
        return view('public.landing');
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

        Mail::to($supportEmail)->queue(new OpesCareNotificationMail(
            mailSubject: "OpesCare Contact: {$subject}",
            bodyText: $body,
        ));

        // Redirect back to the originating page with success flag
        $back = url()->previous();
        return redirect($back)->with('contact_success', true)->with('success', 'Thank you! Your message has been received. We\'ll be in touch shortly.');
    }

    public function status()
    {
        return view('public.status');
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

        $request->session()->regenerate();

        // Route to correct portal based on role
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

    public function submitPatientRegister(Request $request)
    {
        $data = $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'middle_name'          => 'nullable|string|max:100',
            'dob'                  => 'required|date|before:today',
            'sex'                  => 'required|in:male,female,other,unknown',
            'phone'                => 'required|string|max:30',
            'email'                => 'nullable|email|max:180|unique:users,email',
            'country'              => 'nullable|string|max:80',
            'city'                 => 'nullable|string|max:80',
            'national_id'          => 'nullable|string|max:60',
            'emergency_name'       => 'required|string|max:120',
            'emergency_relationship' => 'required|string|max:80',
            'emergency_phone'      => 'required|string|max:30',
            'password'             => 'required|string|min:8|confirmed',
        ]);

        // Duplicate name+dob check (not a hard block — surfaces warning)
        $duplicate = Patient::where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->where('date_of_birth', $data['dob'])
            ->exists();

        if ($duplicate) {
            return redirect()->back()->withInput()
                ->with('error', 'A record matching this identity already exists in our master patient registry. Please contact assistance to recover your existing Health ID.');
        }

        $patient = DB::transaction(function () use ($data) {
            $healthIdSvc = app(HealthIdGeneratorService::class);
            $countryCode = strtoupper(substr($data['country'] ?? 'CM', 0, 2));
            $healthId    = $healthIdSvc->generate($countryCode);

            $patient = Patient::create([
                'health_id'           => $healthId,
                'first_name'          => $data['first_name'],
                'last_name'           => $data['last_name'],
                'middle_name'         => $data['middle_name'] ?? null,
                'date_of_birth'       => $data['dob'],
                'sex'                 => $data['sex'],
                'phone_number'        => $data['phone'],
                'email'               => $data['email'] ?? null,
                'national_id_number'  => $data['national_id'] ?? null,
                'country_code'        => $countryCode,
                'address'             => trim(($data['city'] ?? '') . ', ' . ($data['country'] ?? '')),
                'emergency_contact'   => json_encode([
                    'name'         => $data['emergency_name'],
                    'relationship' => $data['emergency_relationship'],
                    'phone'        => $data['emergency_phone'],
                ]),
                'identity_status'     => 'provisional',
                'verification_status' => 'unverified',
            ]);

            // Create portal user linked to this patient
            $role  = Role::where('name', 'patient')->first();
            $email = $data['email'] ?? ($data['phone'] . '@patients.opescare.local');

            $user = User::create([
                'name'       => $data['first_name'] . ' ' . $data['last_name'],
                'email'      => $email,
                'password'   => Hash::make($data['password']),
                'patient_id' => $patient->id,
                'status'     => 'pending',
            ]);

            if ($role) {
                $user->role_id = $role->id;
                $user->save();
            }

            return $patient;
        });

        // Welcome email
        if ($patient->email) {
            Mail::to($patient->email)->queue(new OpesCareNotificationMail(
                mailSubject: 'Welcome to OpesCare — Your Health ID is ready',
                bodyText: "Hello {$patient->first_name},\n\nYour OpesCare account has been created.\nYour Health ID: {$patient->health_id}\n\nPlease visit a registered facility to complete identity verification.\n\nOpesCare Team",
            ));
        }

        return view('auth.register.patient', [
            'success_profile' => true,
            'health_id'       => $patient->health_id,
        ]);
    }

    public function showGuardianRegister()
    {
        return view('auth.register.guardian');
    }

    public function submitGuardianRegister(Request $request)
    {
        return redirect()->route('register.guardian')->with('success', __('onboarding.guardian.success'));
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

        Mail::to($adminEmail)->queue(new OpesCareNotificationMail(
            mailSubject: "OpesCare Organisation Application: {$data['legal_name']} [{$refCode}]",
            bodyText: $body,
        ));

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

    public function submitDeveloperRegister(Request $request)
    {
        return redirect()->route('register.developer')->with('success', 'Your API/developer request has been submitted. Our interoperability panel will review your system scopes.');
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
        return redirect()->route('login')->with('success', 'Your secure staff account has been activated. You may now sign in.');
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

        return redirect($url)->with('success', 'Authentication complete. Welcome to OpesCare.');
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
                ->with('error', 'Please select a facility to continue.');
        }

        // ✅ Save the chosen facility to session so RequireFacilityContext passes
        session(['active_facility_id' => $facilityId]);

        $url = Auth::check()
            ? app(DashboardProfileService::class)->landingUrlForCurrent()
            : route('login');

        return redirect($url)->with('success', 'Active clinical session established.');
    }
}
