<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
        
        if ($email === 'staff@opescare.com' || $email === 'doctor@opescare.com') {
            return redirect()->route('select-facility');
        }
        
        // Default clinical path asks for 2FA OTP
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

    public function submitVerifyOtp(Request $request)
    {
        $code = implode('', $request->input('otp', []));
        if ($code === '000000') {
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.incorrect'));
        }
        if ($code === '111111') {
            return redirect()->route('otp.verify')->with('error', __('onboarding.otp.errors.expired'));
        }

        return redirect()->route('public.landing')->with('success', 'Authentication complete. Welcome to OpesCare.');
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
        return view('auth.select_facility');
    }

    public function submitSelectFacility(Request $request)
    {
        $facility = $request->input('facility');
        if ($facility === 'suspended') {
            return redirect()->route('select-facility')->with('error', __('onboarding.login.errors.facility_suspended'));
        }

        return redirect()->route('public.landing')->with('success', 'Active clinical session established.');
    }
}
