<?php

namespace Tests\Feature\Public;

use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * The three public request forms that are not account sign-ups.
 *
 * Guardian and developer both told the visitor "your request has been
 * submitted" and then did nothing at all — no validation, no persistence, no
 * notification. The organisation form validated and queued an email, but wrote
 * nothing down, so the reference code it handed the applicant referred to a
 * record that did not exist, and a mail failure lost the application silently.
 *
 * Each now lands in the leads pipeline, which already has an admin screen.
 * These tests pin that a submission survives, and that no password typed into
 * the guardian form is ever stored.
 */
class SignupRequestCaptureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_a_guardian_request_is_recorded(): void
    {
        $this->post('/signup/guardian', [
            'first_name'         => 'Marie',
            'last_name'          => 'Fotso',
            'email'              => 'marie.fotso@example.test',
            'phone'              => '+237670000010',
            'dob'                => '1988-04-02',
            'preferred_language' => 'fr',
            'dep_name'           => 'Jean Fotso',
            'dep_dob'            => '2016-09-11',
            'dep_relationship'   => 'child',
            'dep_sex'            => 'male',
            'access_reason'      => 'I manage my son\'s care.',
            'password'              => 'correct-horse-8',
            'confirm_password'      => 'correct-horse-8',
        ])->assertRedirect(route('register.guardian'));

        $lead = Lead::where('source', 'guardian_signup')->first();

        $this->assertNotNull($lead, 'the caregiver request must be recorded, not discarded');
        $this->assertSame('Marie Fotso', $lead->name);
        $this->assertSame('marie.fotso@example.test', $lead->email);
        $this->assertStringContainsString('Jean Fotso', (string) $lead->message);
        $this->assertSame('new', $lead->status);
    }

    /** A password typed into a request form must never be persisted. */
    public function test_a_guardian_request_never_stores_the_password(): void
    {
        $this->post('/signup/guardian', [
            'first_name'       => 'Marie',
            'last_name'        => 'Fotso',
            'email'            => 'marie2@example.test',
            'phone'            => '+237670000011',
            'dep_name'         => 'Jean Fotso',
            'dep_relationship' => 'child',
            'password'             => 'super-secret-value',
            'confirm_password'     => 'super-secret-value',
        ]);

        foreach (Lead::all() as $lead) {
            $blob = strtolower(json_encode($lead->toArray()));
            $this->assertStringNotContainsString('super-secret-value', $blob);
            $this->assertStringNotContainsString('password', $blob);
        }
    }

    public function test_a_developer_request_is_recorded(): void
    {
        $this->post('/signup/developer', [
            'name'                => 'Ada Byron',
            'email'               => 'ada@vendor.test',
            'phone'               => '+237670000012',
            'organization'        => 'Vendor Systems Sarl',
            'role'                => 'CTO',
            'country'             => 'Cameroon',
            'system_type'         => 'EMR',
            'integration_purpose' => 'Push encounters into the Health ID.',
            'data_flow'           => 'bidirectional',
        ])->assertRedirect(route('register.developer'));

        $lead = Lead::where('source', 'developer_signup')->first();

        $this->assertNotNull($lead, 'the developer request must be recorded, not discarded');
        $this->assertSame('Ada Byron', $lead->name);
        $this->assertSame('developer', $lead->organization_type);
        $this->assertSame('Vendor Systems Sarl', $lead->organization_name);
    }

    public function test_an_organisation_application_survives_a_mail_failure(): void
    {
        $payload = [
            'org_type'       => 'facility',
            'legal_name'     => 'Clinique du Centre Sarl',
            'reg_number'     => 'RC/DLA/2019/B/1234',
            'license_number' => 'MINSANTE-2021-778',
            'address'        => '12 Rue Manga Bell, Douala',
            'main_phone'     => '+237670000013',
            'main_email'     => 'contact@clinique.test',
            'contact_name'   => 'Paul Mbarga',
            'contact_role'   => 'Director',
            'contact_email'  => 'paul@clinique.test',
            'contact_phone'  => '+237670000014',
        ];

        $response = $this->post('/signup/organization', $payload);
        $response->assertOk();

        $lead = Lead::where('source', 'organization_application')->first();

        $this->assertNotNull($lead, 'the application must exist independently of the email');
        $this->assertSame('Clinique du Centre Sarl', $lead->organization_name);

        // The reference code shown to the applicant has to be findable later.
        $ref = $response->viewData('ref_code');
        $this->assertNotEmpty($ref);
        $this->assertStringContainsString($ref, (string) $lead->message);
    }

    public function test_request_forms_reject_incomplete_submissions(): void
    {
        $this->post('/signup/guardian', ['first_name' => 'Marie'])
            ->assertSessionHasErrors(['last_name', 'email']);

        $this->post('/signup/developer', ['name' => 'Ada'])
            ->assertSessionHasErrors(['email', 'organization']);

        $this->assertSame(0, Lead::count());
    }
}
