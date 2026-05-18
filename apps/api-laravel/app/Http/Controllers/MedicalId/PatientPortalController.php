<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\MedicalIdAccessEvent;
use App\Services\Identity\QrTokenService;

class PatientPortalController extends Controller
{
    /**
     * Dashboard / My Health ID
     */
    public function index(Request $request)
    {
        // Mocking an authenticated patient for the portal demo
        // In a real system, this would be auth()->user()->patient
        $patient = Patient::whereNotNull('health_id')->first();
        
        $qrService = new QrTokenService();
        $qrToken = null;
        
        if ($patient) {
            $tokenData = $qrService->generateToken($patient->id);
            $qrToken = $tokenData['raw_token'];
        }

        return view('portals.patient.index', compact('patient', 'qrToken'));
    }

    /**
     * Generate Temporary Access QR
     */
    public function generateTemporaryQr(Request $request)
    {
        // Mock patient
        $patient = Patient::whereNotNull('health_id')->first();
        
        if (!$patient) {
            abort(404);
        }

        $qrService = new QrTokenService();
        // Expires in 60 minutes
        $tokenData = $qrService->generateToken($patient->id, 'temporary_consent_qr', 60);

        return response()->json([
            'raw_token' => $tokenData['raw_token'],
            'expires_in' => 60
        ]);
    }

    /**
     * Patient Appointments
     */
    public function appointments(Request $request)
    {
        $patient = Patient::whereNotNull('health_id')->first();
        return view('portals.patient.appointments', [
            'patient'      => $patient,
            'appointments' => collect([]),
        ]);
    }

    /**
     * View Access Logs
     */
    public function accessLogs(Request $request)
    {
        $patient = Patient::whereNotNull('health_id')->first();
        
        $logs = [];
        if ($patient) {
            $logs = MedicalIdAccessEvent::where('patient_id', $patient->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        return view('portals.patient.logs', compact('patient', 'logs'));
    }
}
