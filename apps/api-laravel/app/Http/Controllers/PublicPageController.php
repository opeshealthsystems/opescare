<?php

namespace App\Http\Controllers;

use App\Mail\OtpMail;
use Illuminate\Http\Request;
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

        // TODO: dispatch ContactMessageReceived / PartnerInquiryReceived mail/job

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
        $email = $request->input('email');

        if ($email === 'suspended@opescare.com') {
            return redirect()->route('account.suspended');
        }
        if ($email === 'pending@opescare.com') {
            return redirect()->route('account.pending');
        }

        // Generate a 6-digit OTP, store it in session with a 10-min expiry
        $code    = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry  = now()->addMinutes(10)->timestamp;

        session([
            'otp_code'   => $code,
            'otp_expiry' => $expiry,
            'otp_email'  => $email,
        ]);

        // Send to Mailpit (or real SMTP in production)
        Mail::to($email)->send(new OtpMail($code, $email));

        return redirect()->route('otp.verify');
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
        // Clinical MVP duplicate patient match check simulation
        $lastName = $request->input('last_name');
        if (strtolower($lastName) === 'duplicate') {
            return redirect()->back()->withInput()->with('error', 'A record matching this identity already exists in our master patient registry. Please contact assistance to recover your existing Health ID.');
        }

        return view('auth.register.patient', [
            'success_profile' => true
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
        return view('auth.register.organization', [
            'success_application' => true,
            'ref_code' => 'OPC-' . strtoupper(bin2hex(random_bytes(4))),
            'legal_name' => $request->input('legal_name', 'Global Care General Hospital')
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

    public function resendOtp(Request $request)
    {
        $email = session('otp_email');

        if (!$email) {
            return redirect()->route('login')->with('error', 'Session expired. Please sign in again.');
        }

        $code   = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expiry = now()->addMinutes(10)->timestamp;

        session(['otp_code' => $code, 'otp_expiry' => $expiry]);

        Mail::to($email)->send(new OtpMail($code, $email));

        return response()->json(['ok' => true]);
    }

    public function submitVerifyOtp(Request $request)
    {
        $submitted = implode('', $request->input('otp', []));
        $stored    = session('otp_code');
        $expiry    = session('otp_expiry');
        $email     = session('otp_email');

        if (!$stored || !$expiry || now()->timestamp > $expiry) {
            session()->forget(['otp_code', 'otp_expiry', 'otp_email']);
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.expired'));
        }

        if ($submitted !== $stored) {
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.incorrect'));
        }

        // OTP valid — clear it and route to the appropriate portal
        session()->forget(['otp_code', 'otp_expiry']);
        session(['auth_email' => $email]);

        return redirect()->to($this->portalRouteForEmail($email))
            ->with('success', 'Authentication complete. Welcome to OpesCare.');
    }

    private function portalRouteForEmail(?string $email): string
    {
        $roleMap = [
            'admin@opescare.com'    => route('portals.admin'),
            'staff@opescare.com'    => route('portals.staff'),
            'doctor@opescare.com'   => route('portals.staff'),
            'clinical@opescare.com' => route('portals.staff'),
        ];

        return $roleMap[$email] ?? route('portals.patient');
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
        $facilities = [
            [
                'id'     => 'fac-001',
                'name'   => 'Metro Clinical Diagnostics Lab',
                'branch' => 'Down-Town Collection Center',
                'role'   => 'Senior Laboratory Technologist',
                'status' => 'active',
            ],
            [
                'id'     => 'fac-002',
                'name'   => 'St. Mary Pediatric Care Clinic',
                'branch' => 'Main Campus',
                'role'   => 'Clinical Staff',
                'status' => 'active',
            ],
            [
                'id'     => 'fac-suspended',
                'name'   => 'Greenfield District Hospital',
                'branch' => 'North Wing',
                'role'   => 'Clinical Staff',
                'status' => 'suspended',
            ],
        ];

        return view('auth.select_facility', compact('facilities'));
    }

    public function submitSelectFacility(Request $request)
    {
        $facility = $request->input('facility');
        if ($facility === 'suspended') {
            return redirect()->route('select-facility')->with('error', __('onboarding.login.errors.facility_suspended'));
        }

        return redirect()->route('portals.staff')->with('success', 'Active clinical session established.');
    }
}
