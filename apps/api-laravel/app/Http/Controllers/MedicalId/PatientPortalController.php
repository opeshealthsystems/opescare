<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\AllergyRecord;
use App\Models\Appointment;
use App\Models\ConsentGrant;
use App\Models\ConsentRequest;
use App\Models\Diagnosis;
use App\Models\ImmunizationRecord;
use App\Models\InsurancePlan;
use App\Models\InsuranceProvider;
use App\Models\LabResult;
use App\Models\MedicalIdAccessEvent;
use App\Models\OfficialDocument;
use App\Models\Patient;
use App\Models\PatientInsurancePolicy;
use App\Models\Prescription;
use App\Services\Identity\QrTokenService;
use App\Services\Portal\PortalContextService;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRMarkupSVG;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientPortalController extends Controller
{
    public function __construct(private readonly PortalContextService $ctx) {}

    private function buildQrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRMarkupSVG::class,
            'outputBase64'    => true,
            'addQuietzone'    => true,
            'quietzoneSize'   => 2,
            'scale'           => 5,
        ]);

        return (new QRCode($options))->render($url);
    }

    /**
     * Resolve the patient record for the authenticated user.
     *
     * Only returns a patient if the authenticated user has a direct patient_id link.
     * Returns null if the user is not linked to any patient.
     */
    private function resolvePatient(): ?Patient
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }
        return $user->patient ?? null;
    }

    /**
     * Returns the dependent patient if guardian context is active, otherwise own patient.
     */
    private function resolveViewingPatient(): ?Patient
    {
        if (request()->attributes->has('viewing_patient')) {
            return request()->attributes->get('viewing_patient');
        }
        return $this->resolvePatient();
    }

    /**
     * Aborts 403 if the guardian is in read_only mode and attempting a write action.
     */
    private function assertWriteAllowed(): void
    {
        $link = request()->attributes->get('guardian_link');
        if ($link && $link->access_level === 'read_only') {
            abort(403, 'Read-only guardian access does not permit this action.');
        }
    }

    /**
     * Dashboard / My Health ID
     */
    public function index(Request $request)
    {
        $patient = $this->resolvePatient();

        $qrToken         = null;
        $staticQrDataUri = null;
        if ($patient) {
            $qrService = new QrTokenService();
            $tokenData = $qrService->generateToken($patient->id);
            $qrToken   = $tokenData['raw_token'];

            try {
                $verifyUrl       = route('verify.qr', ['token' => $qrToken]);
                $staticQrDataUri = $this->buildQrDataUri($verifyUrl);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('QR generation failed', ['error' => $e->getMessage()]);
            }

            // Audit: patient loaded their own health record dashboard
            $this->ctx->auditPatientAccess(
                actionType:   'patient_dashboard_view',
                resourceType: 'Patient',
                resourceId:   $patient->id,
                patientId:    $patient->id,
            );
        }

        // Clinical safety summary for dashboard widgets
        $criticalAllergies = $patient
            ? AllergyRecord::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->whereIn('severity', ['severe', 'high', 'life-threatening'])
                ->get(['id', 'substance', 'severity'])
            : collect();

        $activeAllergies = $patient
            ? AllergyRecord::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->orderByDesc('created_at')
                ->get(['id', 'substance', 'severity'])
            : collect();

        $activeConditions = $patient
            ? Diagnosis::where('patient_id', $patient->id)
                ->whereIn('status', ['active', 'chronic'])
                ->orderByDesc('created_at')
                ->get(['id', 'display_name', 'code', 'status'])
            : collect();

        return view('portals.patient.index', compact(
            'patient', 'qrToken', 'staticQrDataUri',
            'criticalAllergies', 'activeAllergies', 'activeConditions'
        ));
    }

    /**
     * Generate Temporary Access QR
     */
    public function generateTemporaryQr(Request $request)
    {
        $patient = $this->resolvePatient();

        if (!$patient) {
            abort(404);
        }

        $qrService = new QrTokenService();
        $tokenData = $qrService->generateToken($patient->id, 'temporary_consent_qr', 60); // 60-minute TTL; secret stored as SHA-256 hash

        $verifyUrl = route('verify.qr', ['token' => $tokenData['raw_token']]);
        try {
            $qrImage = $this->buildQrDataUri($verifyUrl);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Temp QR generation failed', ['error' => $e->getMessage()]);
            $qrImage = null;
        }

        // Audit: temporary QR generated
        $this->ctx->auditPatientAccess(
            actionType:   'temporary_qr_generated',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        return response()->json([
            'url'        => $verifyUrl,
            'qr_image'   => $qrImage,
            'expires_in' => 3600,
            'expires_at' => now()->addHour()->toIso8601String(),
            'status'     => 'active',
            'token_id'   => $tokenData['model']->id,
        ]);
    }

    /**
     * Download the patient's Health ID card as a print-ready PDF.
     *
     * Generates a fresh static QR token for the card, embeds it as a data URI,
     * then renders a Blade view through DomPDF and streams the result as a PDF
     * download. The token type is 'card_qr' with no expiry (permanent card QR).
     *
     * Every download is audited for MINSANTE compliance.
     */
    public function downloadHealthIdCard(Request $request)
    {
        $patient = $this->resolvePatient();
        abort_if(!$patient, 404);

        // Generate a permanent card QR (no TTL) for printing — distinct from
        // temporary consent QRs so they can be revoked independently.
        $qrService = new QrTokenService();
        $tokenData = $qrService->generateToken($patient->id, 'card_qr', null);

        $qrDataUri = null;
        try {
            $verifyUrl = route('verify.qr', ['token' => $tokenData['raw_token']]);
            $qrDataUri = $this->buildQrDataUri($verifyUrl);
        } catch (\Throwable $e) {
            Log::error('health_id_card_qr_failed', ['patient_id' => $patient->id, 'error' => $e->getMessage()]);
        }

        $this->ctx->auditPatientAccess(
            actionType:   'patient_health_id_card_downloaded',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        $pdf = Pdf::loadView('portals.patient.health-id-card-pdf', [
            'patient'    => $patient,
            'qrDataUri'  => $qrDataUri,
            'issuedAt'   => now()->format('d M Y'),
            'issuedYear' => now()->year,
        ])->setPaper([0, 0, 255.12, 153.07]); // 90mm × 54mm in points (CR80 card size)

        $filename = 'OpesCare-HealthID-' . str_replace('-', '', $patient->health_id) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Revoke a specific QR token (patient-initiated).
     *
     * Called when patient shares a QR accidentally or wants to invalidate an
     * old/lost QR token. The token is looked up by its DB id (not the raw token)
     * so the patient only needs to pass the token_id from generateTemporaryQr().
     */
    public function revokeQrToken(Request $request, string $tokenId)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolvePatient();
        abort_if(!$patient, 403);

        $qrRecord = \App\Models\HealthIdQrToken::where('id', $tokenId)
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->first();

        if (!$qrRecord) {
            return back()->with('warning', 'This QR token was not found or is already inactive.');
        }

        (new QrTokenService())->revokeToken($qrRecord);

        $this->ctx->auditPatientAccess(
            actionType:   'patient_qr_token_revoked',
            resourceType: 'HealthIdQrToken',
            resourceId:   $qrRecord->id,
            patientId:    $patient->id,
        );

        return back()->with('success', 'QR token has been revoked. It can no longer be scanned.');
    }

    /**
     * Revoke all active QR tokens for the patient (used when card is lost/stolen).
     */
    public function revokeAllQrTokens(Request $request)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolvePatient();
        abort_if(!$patient, 403);

        $qrService = new QrTokenService();
        $tokens    = \App\Models\HealthIdQrToken::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();

        $count = 0;
        foreach ($tokens as $token) {
            $qrService->revokeToken($token);
            $count++;
        }

        $this->ctx->auditPatientAccess(
            actionType:   'patient_all_qr_tokens_revoked',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        return back()->with('success', "All {$count} active QR token(s) have been revoked. Your Health ID is still valid — generate a new QR when needed.");
    }

    /**
     * Report a lost or stolen Health ID card.
     *
     * Revokes all active QR tokens so the old card cannot be scanned. The Health ID
     * number itself remains valid — the patient can generate a new QR at any time.
     * This action is audited for MINSANTE compliance.
     */
    public function reportLostCard(Request $request)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolvePatient();
        abort_if(!$patient, 403);

        $request->validate([
            'report_reason' => 'required|string|max:500',
        ]);

        // Revoke all active QR tokens linked to this patient
        $qrService = new QrTokenService();
        $tokens    = \App\Models\HealthIdQrToken::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->get();

        foreach ($tokens as $token) {
            $qrService->revokeToken($token);
        }

        $this->ctx->auditPatientAccess(
            actionType:   'patient_lost_card_reported',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        \Illuminate\Support\Facades\Log::info('patient_lost_card_reported', [
            'patient_id'    => $patient->id,
            'health_id'     => $patient->health_id,
            'report_reason' => $request->input('report_reason'),
            'tokens_revoked'=> $tokens->count(),
            'ip'            => $request->ip(),
        ]);

        return back()->with('success',
            'Your lost card report has been recorded and all active QR codes have been deactivated. '
            . 'Your Health ID number (' . $patient->health_id . ') remains valid — generate a new QR code anytime.'
        );
    }

    /**
     * Patient Appointments
     */
    public function appointments(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $appointments = $patient
            ? Appointment::where('patient_id', $patient->id)
                ->with(['facility:id,name', 'provider:id,first_name,last_name,name'])
                ->orderByDesc('scheduled_at')
                ->paginate(20)
            : collect([]);

        // Audit: patient viewed their appointments list
        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_appointments_view',
                resourceType: 'Appointment',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.appointments', compact('patient', 'appointments'));
    }

    /**
     * Cancel an appointment (patient-initiated)
     */
    public function cancelAppointment(Request $request, string $id)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $appt = Appointment::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        abort_if(!in_array($appt->status, ['scheduled', 'confirmed']), 422, 'This appointment cannot be cancelled.');

        $appt->update([
            'status'               => 'cancelled',
            'cancellation_reason'  => 'Cancelled by patient via portal',
            'cancelled_at'         => now(),
            'cancelled_by_id'      => Auth::id(),
        ]);

        $this->ctx->auditPatientAccess(
            actionType:   'patient_appointment_cancelled',
            resourceType: 'Appointment',
            resourceId:   $appt->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.appointments')
            ->with('success', 'Your appointment has been cancelled.');
    }

    /**
     * Patient Lab Results
     */
    public function labResults(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $labs = $patient
            ? LabResult::where('patient_id', $patient->id)
                ->with('labOrder')
                ->orderByDesc('resulted_at')
                ->paginate(25)
            : collect([]);

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_labs_view',
                resourceType: 'LabResult',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.labs', compact('patient', 'labs'));
    }

    /**
     * Patient Prescriptions
     */
    public function prescriptions(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $prescriptions = $patient
            ? Prescription::where('patient_id', $patient->id)
                ->with(['items', 'facility'])
                ->orderByDesc('prescribed_at')
                ->paginate(15)
            : collect([]);

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_prescriptions_view',
                resourceType: 'Prescription',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.prescriptions', compact('patient', 'prescriptions'));
    }

    /**
     * Request a prescription refill (patient-initiated)
     */
    public function requestRefill(Request $request, string $id)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $rx = Prescription::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        $this->ctx->auditPatientAccess(
            actionType:   'patient_refill_requested',
            resourceType: 'Prescription',
            resourceId:   $rx->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.prescriptions')
            ->with('success', 'Refill request submitted. Your prescribing facility will review it and contact you within 1–2 business days.');
    }

    /**
     * Patient Consent Requests
     */
    public function consentRequests(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $consentRequests = $patient
            ? ConsentRequest::where('patient_id', $patient->id)
                ->with('requestingFacility')
                ->orderByDesc('created_at')
                ->paginate(20)
            : collect([]);

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_consent_view',
                resourceType: 'ConsentRequest',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.consent', compact('patient', 'consentRequests'));
    }

    /**
     * Approve a consent request
     */
    public function approveConsent(Request $request, string $id)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $req = ConsentRequest::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        abort_if($req->status !== 'pending', 422, 'Request is not pending.');

        \Illuminate\Support\Facades\DB::transaction(function () use ($req, $patient) {
            $req->update(['status' => 'approved']);

            ConsentGrant::create([
                'patient_id'         => $patient->id,
                'facility_id'        => $req->requesting_facility_id,
                'consent_request_id' => $req->id,
                'authorizing_actor'  => 'patient',
                'scope'              => $req->requested_scope ?? [],
                'status'             => 'active',
                'expires_at'         => now()->addMinutes($req->duration_minutes ?? 1440),
            ]);
        });

        $this->ctx->auditPatientAccess(
            actionType:   'patient_consent_approved',
            resourceType: 'ConsentRequest',
            resourceId:   $req->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.consent')->with('success', 'Consent approved.');
    }

    /**
     * Deny a consent request
     */
    public function denyConsent(Request $request, string $id)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $req = ConsentRequest::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        abort_if($req->status !== 'pending', 422, 'Request is not pending.');

        $req->update(['status' => 'denied']);

        $this->ctx->auditPatientAccess(
            actionType:   'patient_consent_denied',
            resourceType: 'ConsentRequest',
            resourceId:   $req->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.consent')->with('success', 'Consent denied.');
    }

    /**
     * Revoke a previously approved consent grant
     */
    public function revokeConsent(Request $request, string $id)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $req = ConsentRequest::where('id', $id)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        abort_if($req->status !== 'approved', 422, 'Only approved consents can be revoked.');

        DB::transaction(function () use ($req, $patient) {
            $req->update(['status' => 'revoked']);
            ConsentGrant::where('consent_request_id', $req->id)
                ->where('patient_id', $patient->id)
                ->where('status', 'active')
                ->update(['status' => 'revoked']);
        });

        $this->ctx->auditPatientAccess(
            actionType:   'patient_consent_revoked',
            resourceType: 'ConsentRequest',
            resourceId:   $req->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.consent')->with('success', 'Consent access revoked.');
    }

    /**
     * Patient Official Documents
     */
    public function documents(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $documents = $patient
            ? OfficialDocument::where('patient_id', $patient->id)
                ->select(['id', 'title', 'document_type', 'document_number', 'status', 'issued_at', 'expires_at', 'sensitivity_level', 'pdf_path'])
                ->orderByDesc('issued_at')
                ->paginate(20)
            : collect([]);

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_documents_view',
                resourceType: 'OfficialDocument',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.documents', compact('patient', 'documents'));
    }

    /**
     * Download a released official document PDF
     */
    public function documentDownload(Request $request, string $id)
    {
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $doc = OfficialDocument::where('id', $id)
            ->where('patient_id', $patient->id)
            ->where('status', 'released')
            ->firstOrFail();

        if (!$doc->pdf_path || !Storage::exists($doc->pdf_path)) {
            return back()->with('warning', 'This document is not yet available for download. Please check back later.');
        }

        $this->ctx->auditPatientAccess(
            actionType:   'patient_document_downloaded',
            resourceType: 'OfficialDocument',
            resourceId:   $doc->id,
            patientId:    $patient->id,
        );

        $filename = Str::slug($doc->title ?? $doc->document_type) . '-' . ($doc->document_number ?? $doc->id) . '.pdf';

        return Storage::download($doc->pdf_path, $filename, ['Content-Type' => 'application/pdf']);
    }

    /**
     * Patient Profile & Privacy Settings
     */
    public function profile(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $allergies = $patient
            ? AllergyRecord::where('patient_id', $patient->id)
                ->orderByDesc('created_at')
                ->get(['id', 'substance', 'severity', 'status'])
            : collect();

        $conditions = $patient
            ? Diagnosis::where('patient_id', $patient->id)
                ->whereIn('status', ['active', 'chronic'])
                ->orderByDesc('created_at')
                ->get(['id', 'display_name', 'code', 'status'])
            : collect();

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_profile_view',
                resourceType: 'Patient',
                resourceId:   $patient->id,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.profile', compact('patient', 'allergies', 'conditions'));
    }

    /**
     * Update Patient Profile & Privacy Settings
     */
    public function updateProfile(Request $request)
    {
        $this->assertWriteAllowed();
        $patient = $this->resolveViewingPatient();
        abort_if(!$patient, 403);

        $validated = $request->validate([
            'phone_number'                   => 'sometimes|nullable|string|max:30',
            'email'                          => 'sometimes|nullable|email|max:255',
            'address'                        => 'sometimes|nullable|string|max:500',
            'blood_group'                    => 'sometimes|nullable|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'date_of_birth'                  => 'sometimes|nullable|date|before:today',
            'emergency_contact.name'         => 'sometimes|nullable|string|max:100',
            'emergency_contact.phone'        => 'sometimes|nullable|string|max:30',
            'emergency_contact.relationship' => 'sometimes|nullable|string|max:50',
            'privacy_require_consent'        => 'nullable|boolean',
            'privacy_emergency_access'       => 'nullable|boolean',
        ]);

        $updateData = [];

        foreach (['phone_number', 'email', 'address', 'blood_group', 'date_of_birth'] as $field) {
            if (array_key_exists($field, $validated)) {
                $updateData[$field] = $validated[$field];
            }
        }

        if (isset($validated['emergency_contact'])) {
            $updateData['emergency_contact'] = $validated['emergency_contact'];
        }

        $privacyPrefs = $patient->privacy_preferences ?? [];
        if (array_key_exists('privacy_require_consent', $validated)) {
            $privacyPrefs['require_consent_for_full_record'] = (bool) $validated['privacy_require_consent'];
        }
        if (array_key_exists('privacy_emergency_access', $validated)) {
            $privacyPrefs['emergency_access_allowed'] = (bool) $validated['privacy_emergency_access'];
        }
        if ($privacyPrefs !== ($patient->privacy_preferences ?? [])) {
            $updateData['privacy_preferences'] = $privacyPrefs;
        }

        if (!empty($updateData)) {
            $patient->update($updateData);
        }

        $this->ctx->auditPatientAccess(
            actionType:   'patient_profile_updated',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        return redirect()->route('portals.patient.profile')->with('success', 'Profile updated successfully.');
    }

    /**
     * Patient Allergies
     */
    public function allergies(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $allergies = $patient
            ? AllergyRecord::where('patient_id', $patient->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_allergies_view',
                resourceType: 'AllergyRecord',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.allergies', compact('patient', 'allergies'));
    }

    /**
     * Patient Clinical History (Diagnoses / Conditions)
     */
    public function clinicalHistory(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $conditions = $patient
            ? Diagnosis::where('patient_id', $patient->id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_clinical_history_view',
                resourceType: 'Diagnosis',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.clinical', compact('patient', 'conditions'));
    }

    /**
     * Patient Immunization History
     */
    public function immunizations(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $immunizations = $patient
            ? ImmunizationRecord::where('patient_id', $patient->id)
                ->orderByDesc('administered_at')
                ->get()
            : collect();

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_immunizations_view',
                resourceType: 'ImmunizationRecord',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.immunizations', compact('patient', 'immunizations'));
    }

    /**
     * View Access Logs
     */
    public function accessLogs(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        $logs = collect([]);
        if ($patient) {
            $logs = MedicalIdAccessEvent::where('patient_id', $patient->id)
                ->orderBy('created_at', 'desc')
                ->paginate(25);

            // Audit: patient viewed their own access log
            $this->ctx->auditPatientAccess(
                actionType:   'patient_access_log_view',
                resourceType: 'MedicalIdAccessEvent',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.logs', compact('patient', 'logs'));
    }

    // ─── Insurance Marketplace ────────────────────────────────────────────────

    /**
     * GET /portals/patient/insurance
     * Shows the patient's current policies + a marketplace banner.
     */
    public function insuranceMarketplace(Request $request)
    {
        $patient = $this->resolveViewingPatient();

        // Active purchasable providers
        $providers = InsuranceProvider::where('status', 'active')
            ->with(['activePlans' => fn ($q) => $q->where('is_purchasable', true)])
            ->get()
            ->filter(fn ($p) => $p->activePlans->isNotEmpty())
            ->values();

        // Patient's existing policies
        $myPolicies = $patient
            ? PatientInsurancePolicy::where('patient_id', $patient->id)
                ->with(['plan:id,name,plan_type,insurance_provider_id', 'plan.provider:id,name'])
                ->orderByDesc('effective_date')
                ->get()
            : collect([]);

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_insurance_marketplace_view',
                resourceType: 'InsuranceMarketplace',
                resourceId:   null,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.insurance.marketplace',
            compact('patient', 'providers', 'myPolicies'));
    }

    /**
     * GET /portals/patient/insurance/plans/{id}
     * Shows full plan detail with enroll button.
     */
    public function insurancePlanDetail(Request $request, string $id)
    {
        $plan = InsurancePlan::with('provider')
            ->where('id', $id)
            ->where('status', 'active')
            ->where('is_purchasable', true)
            ->firstOrFail();

        $patient = $this->resolveViewingPatient();

        $alreadyEnrolled = $patient
            ? PatientInsurancePolicy::where('patient_id', $patient->id)
                ->where('insurance_plan_id', $plan->id)
                ->where('status', 'active')
                ->exists()
            : false;

        return view('portals.patient.insurance.plan_detail',
            compact('patient', 'plan', 'alreadyEnrolled'));
    }

    /**
     * POST /portals/patient/insurance/plans/{id}/purchase
     * Self-enroll via the web portal.
     */
    public function insurancePurchase(Request $request, string $id)
    {
        $plan = InsurancePlan::where('id', $id)
            ->where('status', 'active')
            ->where('is_purchasable', true)
            ->firstOrFail();

        $patient = $this->resolveViewingPatient();
        if (!$patient) {
            abort(403, 'No patient profile linked to this account.');
        }

        // Prevent duplicate active enrollment
        $existing = PatientInsurancePolicy::where('patient_id', $patient->id)
            ->where('insurance_plan_id', $plan->id)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            return back()->with('warning', 'You already have an active policy for this plan.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:mobile_money,card,bank_transfer',
        ]);

        PatientInsurancePolicy::create([
            'patient_id'              => $patient->id,
            'insurance_plan_id'       => $plan->id,
            'policy_number'           => 'POL-' . strtoupper(Str::random(10)),
            'relationship_to_primary' => 'self',
            'effective_date'          => now()->toDateString(),
            'expiry_date'             => now()->addYear()->toDateString(),
            'status'                  => 'pending',
            'notes'                   => 'Self-enrolled via patient portal. Payment: ' . $validated['payment_method'],
        ]);

        return redirect()
            ->route('portals.patient.insurance')
            ->with('success', 'Enrollment submitted! Your policy is pending activation and will be confirmed within 1–2 business days.');
    }
}
