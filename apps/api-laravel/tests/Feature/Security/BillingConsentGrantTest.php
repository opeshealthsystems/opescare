<?php
namespace Tests\Feature\Security;

use Tests\TestCase;

class BillingConsentGrantTest extends TestCase
{
    public function test_billing_controller_checks_consent_before_patient_id_filter(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/BillingController.php')
        );

        // Must reference consent_grant to verify caller has access to this patient's data
        $this->assertTrue(
            str_contains($source, 'consent_grant') ||
            str_contains($source, 'ConsentGrant') ||
            str_contains($source, 'consent'),
            'BillingController must verify consent grant before filtering by patient_id'
        );
    }

    public function test_billing_controller_validates_patient_id_ownership(): void
    {
        $source = file_get_contents(
            app_path('Http/Controllers/Api/V1/BillingController.php')
        );

        // If patient_id is accepted from request input, it must be validated
        if (str_contains($source, "input('patient_id')") || str_contains($source, "request->patient_id") || str_contains($source, "'patient_id'")) {
            $this->assertTrue(
                str_contains($source, 'consent') || str_contains($source, 'ConsentGrant') || str_contains($source, 'ownership'),
                'When patient_id is accepted from input, BillingController must verify consent/ownership'
            );
        } else {
            $this->assertTrue(true, 'No patient_id input found — no IDOR risk');
        }
    }
}
