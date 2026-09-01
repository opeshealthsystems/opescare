<?php

return [
    'login' => [
        'title' => 'Sign In',
        'subtitle' => 'Access your OpesCare portal',
        'email' => 'Email Address',
        'password' => 'Password',
        'remember' => 'Remember me',
        'forgot' => 'Forgot password?',
        'submit' => 'Sign In',
        'no_account' => "Don't have an account?",
        'register' => 'Get Started',
    ],
    'register' => [
        'title' => 'Get Started with OpesCare',
        'subtitle' => 'Choose your account type to begin',
        'patient_title' => 'For Patients',
        'patient_desc' => 'Get your digital Health ID and manage your medical history.',
        'hospital_title' => 'For Institutions',
        'hospital_desc' => 'Connect your hospital, clinic, lab or pharmacy to the OpesCare network.',
        'submit_patient' => 'Register as Patient',
        'submit_hospital' => 'Onboard Institution',
        'already_have' => 'Already have an account?',
        'login' => 'Sign In',
        
        'patient' => [
            'title' => 'Patient Registration',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'dob' => 'Date of Birth',
            'gender' => 'Sex',
            'phone' => 'Phone Number',
            'email' => 'Email Address',
            'password' => 'Create Password',
            'confirm_password' => 'Confirm Password',
            'submit' => 'Create Health ID',
        ],
        
        'hospital' => [
            'title' => 'Institutional Onboarding',
            'name' => 'Facility Name',
            'type' => 'Facility Type',
            'license' => 'License Number',
            'admin_name' => 'Admin Full Name',
            'admin_email' => 'Admin Email',
            'admin_phone' => 'Contact Phone',
            'submit' => 'Submit Onboarding Request',
        ],
    ],
    /* ── Portal frozen out of the current release ───── */
    'portal_unavailable' => [
        'page_title'    => 'Portal unavailable | OpesCare',
        'title'         => 'This portal is not part of the current release.',
        'body'          => 'Your account is active and your sign-in worked. The portal for your role is not switched on in this version of OpesCare, so there is nothing for you to open yet.',
        'next'          => 'Nothing is wrong with your account and no data has been lost. Your access will resume the moment the module is enabled.',
        'cta_contact'   => 'Contact support',
        'cta_signout'   => 'Sign out',
        'signed_in_as'  => 'Signed in as :email',
    ],
];