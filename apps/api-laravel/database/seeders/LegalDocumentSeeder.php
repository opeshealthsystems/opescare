<?php

namespace Database\Seeders;

use App\Models\LegalDocument;
use App\Models\LegalDocumentVersion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * LegalDocumentSeeder
 *
 * Seeds all required and supplementary legal documents for OpesCare.
 * Idempotent — safe to run multiple times. Skips documents that already
 * have a current version (does NOT overwrite existing published content).
 *
 * Documents published here are minimal policy stubs — production deployments
 * MUST replace content with jurisdiction-specific legal text before go-live.
 *
 * Supports: LegalDocumentService::REQUIRED_DOCUMENTS + supplementary set.
 */
class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = $this->documentCatalog();

        foreach ($documents as $slug => $config) {
            $document = LegalDocument::firstOrCreate(
                ['slug' => $slug],
                [
                    'title'         => $config['title'],
                    'document_type' => $config['type'],
                    'language'      => 'en',
                    'is_active'     => true,
                ]
            );

            // Skip if a current version already exists
            if ($document->versions()->where('is_current', true)->exists()) {
                continue;
            }

            // Mark any non-current versions as non-current just in case
            $document->versions()->update(['is_current' => false]);

            $contentHtml = $this->stubContent($config['title'], $config['stub_summary']);

            LegalDocumentVersion::create([
                'legal_document_id'     => $document->id,
                'version'               => '1.0',
                'content_html'          => $contentHtml,
                'content_hash'          => hash('sha256', $contentHtml),
                'is_current'            => true,
                'requires_reacceptance' => false,
                'change_summary'        => 'Initial version',
                'published_by'          => null,
                'published_at'          => now(),
                'effective_at'          => now(),
            ]);
        }
    }

    // ── Document Catalog ──────────────────────────────────────────────────────

    private function documentCatalog(): array
    {
        return [
            // ── Core Required Documents ────────────────────────────────────
            'terms-of-use' => [
                'title' => 'Terms of Use',
                'type'  => 'terms',
                'stub_summary' => 'These Terms of Use govern access to and use of the OpesCare platform by patients, healthcare providers, and partner organisations. By accessing OpesCare, you agree to comply with these terms.',
            ],
            'privacy-policy' => [
                'title' => 'Privacy Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This Privacy Policy explains how OpesCare collects, processes, stores, and protects personal and health data in accordance with applicable data protection law.',
            ],
            'patient-consent-policy' => [
                'title' => 'Patient Consent Policy',
                'type'  => 'consent',
                'stub_summary' => 'This policy describes how OpesCare obtains, manages, and respects patient consent for data processing, sharing, and clinical care activities.',
            ],
            'data-processing-agreement' => [
                'title' => 'Data Processing Agreement',
                'type'  => 'dpa',
                'stub_summary' => 'This Data Processing Agreement (DPA) governs the processing of personal data by OpesCare on behalf of facilities and partner organisations acting as data controllers.',
            ],
            'facility-agreement' => [
                'title' => 'Facility Agreement',
                'type'  => 'facility_agreement',
                'stub_summary' => 'This Facility Agreement governs the relationship between OpesCare and healthcare facilities onboarded to the platform, including service obligations, data responsibilities, and go-live requirements.',
            ],
            'api-developer-terms' => [
                'title' => 'API / Developer Terms',
                'type'  => 'api_terms',
                'stub_summary' => 'These API Developer Terms govern access to the OpesCare API, including permitted uses, rate limits, production approval requirements, and security obligations.',
            ],

            // ── Supplementary Documents ───────────────────────────────────
            'cookie-policy' => [
                'title' => 'Cookie Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This Cookie Policy explains what cookies OpesCare uses, why we use them, and how users can manage cookie preferences on our web portals and patient-facing applications.',
            ],
            'pharmacy-partner-agreement' => [
                'title' => 'Pharmacy Partner Agreement',
                'type'  => 'facility_agreement',
                'stub_summary' => 'This agreement governs the terms under which pharmacy partners integrate with OpesCare for prescription dispensing, stock management, and patient medication records.',
            ],
            'laboratory-partner-agreement' => [
                'title' => 'Laboratory Partner Agreement',
                'type'  => 'facility_agreement',
                'stub_summary' => 'This agreement governs the terms under which laboratory partners integrate with OpesCare for lab order management, result reporting, and LOINC-mapped result delivery.',
            ],
            'insurance-partner-agreement' => [
                'title' => 'Insurance Partner Agreement',
                'type'  => 'facility_agreement',
                'stub_summary' => 'This agreement governs the terms under which insurance partners and review organisations access OpesCare for claims processing, pre-authorisation, and minimum necessary clinical data access.',
            ],
            'public-health-data-sharing-policy' => [
                'title' => 'Public Health Data Sharing Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy defines the conditions under which de-identified or aggregate health data from OpesCare may be shared with public health authorities, including anonymisation standards and reporting protocols.',
            ],
            'acceptable-use-policy' => [
                'title' => 'Acceptable Use Policy',
                'type'  => 'terms',
                'stub_summary' => 'This Acceptable Use Policy defines permitted and prohibited uses of the OpesCare platform for all user roles, including clinical staff, patients, developers, and administrative users.',
            ],
            'data-retention-policy' => [
                'title' => 'Data Retention Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy defines the retention periods for all categories of health and operational data held by OpesCare, including deletion procedures and audit requirements.',
            ],
            'data-deletion-policy' => [
                'title' => 'Data Deletion Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy defines how patients and organisations may request deletion of their data, the criteria that apply, and the process for fulfilling deletion requests under applicable law.',
            ],
            'patient-rights-policy' => [
                'title' => 'Patient Rights Policy',
                'type'  => 'consent',
                'stub_summary' => 'This policy explains the rights patients have regarding their health data held by OpesCare, including the right to access, correct, restrict, port, and request deletion of their records.',
            ],
            'incident-breach-notification-policy' => [
                'title' => 'Incident and Breach Notification Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy defines how OpesCare detects, responds to, and notifies affected parties of security incidents and data breaches, including regulatory notification timelines.',
            ],
            'clinical-disclaimer' => [
                'title' => 'Clinical Disclaimer',
                'type'  => 'consent',
                'stub_summary' => 'OpesCare is a health information management platform. It does not provide medical advice, diagnosis, or treatment. All clinical decisions remain the sole responsibility of licensed healthcare professionals.',
            ],
            'cdss-disclaimer' => [
                'title' => 'Clinical Decision Support Disclaimer',
                'type'  => 'consent',
                'stub_summary' => 'Clinical Decision Support System (CDSS) alerts and recommendations provided by OpesCare are decision-support tools only. They do not replace professional clinical judgment and must never be interpreted as diagnostic or prescriptive authority.',
            ],
            'telemedicine-disclaimer' => [
                'title' => 'Telemedicine Disclaimer',
                'type'  => 'consent',
                'stub_summary' => 'This disclaimer governs telemedicine consultations conducted through OpesCare, including limitations of remote care, patient consent requirements, and data security during virtual consultations.',
            ],
            'care-access-map-disclaimer' => [
                'title' => 'Care Access Map Disclaimer',
                'type'  => 'terms',
                'stub_summary' => 'The OpesCare Care Access Map is provided for informational purposes only. Facility, pharmacy, and laboratory listings are not endorsements. Availability and services should be verified directly with the listed facility.',
            ],
            'support-access-policy' => [
                'title' => 'Support Access Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy governs when and how OpesCare support staff may access facility or patient data for troubleshooting purposes, including audit requirements and patient notification obligations.',
            ],
            'research-data-access-policy' => [
                'title' => 'Research Data Access Policy',
                'type'  => 'privacy',
                'stub_summary' => 'This policy governs requests to access de-identified OpesCare data for research purposes, including ethics approval requirements, data sharing agreements, and prohibited re-identification activities.',
            ],
        ];
    }

    // ── Stub Content Generator ────────────────────────────────────────────────

    private function stubContent(string $title, string $summary): string
    {
        $effectiveDate = now()->format('d F Y');

        return <<<HTML
<div class="legal-document">
    <h1>{$title}</h1>
    <p class="legal-meta"><strong>Effective Date:</strong> {$effectiveDate} &nbsp;|&nbsp; <strong>Version:</strong> 1.0</p>

    <div class="legal-notice" style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:14px 18px;margin:16px 0;font-size:0.9em;">
        <strong>⚠ Stub Document — For Internal Use Only</strong><br>
        This document is a placeholder stub. Before go-live, this content <strong>MUST</strong> be replaced
        with jurisdiction-specific legal text reviewed by qualified legal counsel.
    </div>

    <h2>Overview</h2>
    <p>{$summary}</p>

    <h2>Scope</h2>
    <p>This document applies to all users, facilities, and partner organisations that access or use the OpesCare platform.</p>

    <h2>Governing Law</h2>
    <p>This document is governed by the laws of the jurisdiction in which the relevant OpesCare deployment operates. Specific jurisdiction details must be inserted by the deploying organisation's legal team.</p>

    <h2>Contact</h2>
    <p>For questions about this document, contact the OpesCare Privacy Officer or Legal Team at the contact details provided during your facility onboarding.</p>

    <h2>Updates</h2>
    <p>OpesCare reserves the right to update this document. Users will be notified of material changes and may be required to re-accept updated terms before continuing to use the platform.</p>
</div>
HTML;
    }
}
