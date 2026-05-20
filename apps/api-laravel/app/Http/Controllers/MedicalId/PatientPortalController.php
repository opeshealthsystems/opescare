<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\MedicalIdAccessEvent;
use App\Models\Patient;
use App\Services\Identity\QrTokenService;
use App\Services\Portal\PortalContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPortalController extends Controller
{
    public function __construct(private readonly PortalContextService $ctx) {}

    /**
     * Resolve the patient record for the authenticated user.
     *
     * For real patient users, the patient is linked via the user account.
     * Falls back to first available patient (demo compatibility) if no
     * direct relationship exists yet.
     */
    private function resolvePatient(): ?Patient
    {
        $user = Auth::user();

        // Preferred: user has a directly linked patient record
        if ($user && method_exists($user, 'patient') && $user->patient) {
            return $user->patient;
        }

        // Fallback: find patient by user's email (common account-linking pattern)
        if ($user && $user->email) {
            $patient = Patient::where('email', $user->email)->first();
            if ($patient) {
                return $patient;
            }
        }

        // Demo fallback: first seeded patient with a Health ID
        return Patient::whereNotNull('health_id')->first();
    }

    /**
     * Dashboard / My Health ID
     */
    public function index(Request $request)
    {
        $patient = $this->resolvePatient();

        $qrToken = null;
        if ($patient) {
            $qrService = new QrTokenService();
            $tokenData = $qrService->generateToken($patient->id);
            $qrToken   = $tokenData['raw_token'];

            // Audit: patient loaded their own health record dashboard
            $this->ctx->auditPatientAccess(
                actionType:   'patient_dashboard_view',
                resourceType: 'Patient',
                resourceId:   $patient->id,
                patientId:    $patient->id,
            );
        }

        return view('portals.patient.index', compact('patient', 'qrToken'));
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
        $tokenData = $qrService->generateToken($patient->id, 'temporary_consent_qr', 60);

        // Audit: temporary QR generated
        $this->ctx->auditPatientAccess(
            actionType:   'temporary_qr_generated',
            resourceType: 'Patient',
            resourceId:   $patient->id,
            patientId:    $patient->id,
        );

        return response()->json([
            'raw_token'  => $tokenData['raw_token'],
            'expires_in' => 60,
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
