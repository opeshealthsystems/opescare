<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ConsentGrant;
use App\Models\ConsentRequest;
use App\Models\LabResult;
use App\Models\MedicalIdAccessEvent;
use App\Models\OfficialDocument;
use App\Models\Patient;
use App\Models\Prescription;
use App\Services\Identity\QrTokenService;
use App\Services\Portal\PortalContextService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPortalController extends Controller
{
    public function __construct(private readonly PortalContextService $ctx) {}

    private function buildQrDataUri(string $url): string
    {
        $options = new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'outputBase64'    => true,
            'scale'           => 8,
            'addQuietzone'    => true,
            'quietzoneSize'   => 2,
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
     * Dashboard / My Health ID
     */
    public function index(Request $request)
    {
        $patient = $this->resolvePatient();

        $qrToken       = null;
        $staticQrDataUri = null;
        if ($patient) {
            $qrService = new QrTokenService();
            $tokenData = $qrService->generateToken($patient->id);
            $qrToken   = $tokenData['raw_token'];

            $verifyUrl     = route('verify.qr', ['token' => $qrToken]);
            $staticQrDataUri = $this->buildQrDataUri($verifyUrl);

            // Audit: patient loaded their own health record dashboard
            $this->ctx->auditPatientAccess(
                actionType:   'patient_dashboard_view',
                resourceType: 'Patient',
                resourceId:   $patient->id,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.index', compact('patient', 'qrToken', 'staticQrDataUri'));
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
        $qrImage   = $this->buildQrDataUri($verifyUrl);

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
        ]);
    }

    /**
     * Patient Appointments
     */
    public function appointments(Request $request)
    {
        $patient = $this->resolvePatient();

        $appointments = $patient
            ? Appointment::where('patient_id', $patient->id)
                ->orderByDesc('scheduled_at')
                ->limit(50)
                ->get()
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
     * Patient Lab Results
     */
    public function labResults(Request $request)
    {
        $patient = $this->resolvePatient();

        $labs = $patient
            ? LabResult::where('patient_id', $patient->id)
                ->with('labOrder')
                ->orderByDesc('resulted_at')
                ->limit(100)
                ->get()
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
        $patient = $this->resolvePatient();

        $prescriptions = $patient
            ? Prescription::where('patient_id', $patient->id)
                ->with(['items', 'facility'])
                ->orderByDesc('prescribed_at')
                ->limit(50)
                ->get()
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
     * Patient Consent Requests
     */
    public function consentRequests(Request $request)
    {
        $patient = $this->resolvePatient();

        $consentRequests = $patient
            ? ConsentRequest::where('patient_id', $patient->id)
                ->with('requestingFacility')
                ->orderByDesc('created_at')
                ->limit(50)
                ->get()
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
        $patient = $this->resolvePatient();
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
        $patient = $this->resolvePatient();
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
     * Patient Official Documents
     */
    public function documents(Request $request)
    {
        $patient = $this->resolvePatient();

        $documents = $patient
            ? OfficialDocument::where('patient_id', $patient->id)
                ->select(['id', 'title', 'document_type', 'document_number', 'status', 'issued_at', 'expires_at', 'sensitivity_level'])
                ->orderByDesc('issued_at')
                ->limit(50)
                ->get()
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
     * Patient Profile & Privacy Settings
     */
    public function profile(Request $request)
    {
        $patient = $this->resolvePatient();

        if ($patient) {
            $this->ctx->auditPatientAccess(
                actionType:   'patient_profile_view',
                resourceType: 'Patient',
                resourceId:   $patient->id,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.profile', compact('patient'));
    }

    /**
     * Update Patient Profile & Privacy Settings
     */
    public function updateProfile(Request $request)
    {
        $patient = $this->resolvePatient();
        abort_if(!$patient, 403);

        $validated = $request->validate([
            'phone_number'                   => 'sometimes|nullable|string|max:30',
            'email'                          => 'sometimes|nullable|email|max:255',
            'address'                        => 'sometimes|nullable|string|max:500',
            'emergency_contact.name'         => 'sometimes|nullable|string|max:100',
            'emergency_contact.phone'        => 'sometimes|nullable|string|max:30',
            'emergency_contact.relationship' => 'sometimes|nullable|string|max:50',
            'privacy_require_consent'        => 'nullable|boolean',
            'privacy_emergency_access'       => 'nullable|boolean',
        ]);

        $updateData = [];

        foreach (['phone_number', 'email', 'address'] as $field) {
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
     * View Access Logs
     */
    public function accessLogs(Request $request)
    {
        $patient = $this->resolvePatient();

        $logs = [];
        if ($patient) {
            $logs = MedicalIdAccessEvent::where('patient_id', $patient->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

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
}
