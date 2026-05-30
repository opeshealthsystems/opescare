<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientAccessToken;
use App\Models\PatientOtpCode;
use App\Modules\Notifications\Services\SmsNotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MobileAuthController extends Controller
{
    public function __construct(private SmsNotificationService $sms) {}

    /**
     * Step 1: Patient provides phone + PIN.
     * If PIN matches, we send an OTP.
     * If patient has no PIN yet, we set it on first login (bootstrap flow).
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number'  => 'required|string',
            'pin'           => 'required|string|min:4|max:8',
            'date_of_birth' => 'sometimes|date_format:Y-m-d',
        ]);

        // phone_number is stored encrypted; use the keyed hash for DB lookup
        $patient = Patient::findByPhone($request->phone_number);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        if (is_null($patient->pin_hash)) {
            // Bootstrap: require date_of_birth to verify identity before setting PIN
            if (!$request->has('date_of_birth') || $request->date_of_birth !== $patient->date_of_birth?->format('Y-m-d')) {
                return response()->json(['message' => 'Identity verification required. Please provide your date of birth.'], 422);
            }
            $patient->update(['pin_hash' => Hash::make($request->pin)]);
        } elseif (!Hash::check($request->pin, $patient->pin_hash)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // Generate a 6-digit OTP, store its hash, expire in 10 minutes
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PatientOtpCode::create([
            'phone_number' => $patient->phone_number,
            'code_hash'    => Hash::make($otp),
            'expires_at'   => Carbon::now()->addMinutes(10),
        ]);

        try {
            $this->sms->send(
                $patient->phone_number,
                "Your OpesCare verification code is: {$otp}. Valid for 10 minutes."
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OpesCare SMS delivery failed', [
                'phone' => $patient->phone_number,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'OTP sent to your registered phone number.'], 200);
    }

    /**
     * Step 2: Patient provides phone + OTP.
     * Issues a 24-hour access token on success.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone_number' => 'required|string',
            'otp'          => 'required|string|size:6',
        ]);

        // phone_number is stored encrypted; use the keyed hash for DB lookup
        $patient = Patient::findByPhone($request->phone_number);

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        // Find the most recent unused, unexpired OTP for this phone
        $otpRecord = PatientOtpCode::where('phone_number', $request->phone_number)
            ->whereNull('used_at')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->first();

        if (!$otpRecord || !Hash::check($request->otp, $otpRecord->code_hash)) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 401);
        }

        // Mark OTP as used
        $otpRecord->update(['used_at' => Carbon::now()]);

        // Issue a 24-hour access token
        $rawToken = 'pat_' . Str::random(40);
        PatientAccessToken::create([
            'patient_id'   => $patient->id,
            'token_hash'   => Hash::make($rawToken),
            'token_prefix' => substr($rawToken, 0, 12),
            'expires_at'   => Carbon::now()->addHours(24),
        ]);

        return response()->json([
            'status'       => 'authenticated',
            'access_token' => $rawToken,
            'token_type'   => 'Bearer',
            'expires_in'   => 86400,
            'patient_id'   => $patient->id,
        ], 200);
    }

    /**
     * Email + password login — uses the same credentials as the patient portal.
     * Finds the patient record by email match, verifies the portal password,
     * then issues a 24-hour mobile access token directly (no OTP step).
     *
     * POST /mobile/auth/login-email
     * Body: { email, password }
     */
    public function loginWithCredentials(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email|max:180',
            'password' => 'required|string|min:4',
        ]);

        // Step 1: Verify credentials against the users table (same as web portal)
        if (! Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $user = Auth::user();
        Auth::logout(); // We only used Auth to verify — mobile uses its own token system

        // Step 2: Find the matching patient record by email
        $patient = Patient::where('email', $request->email)->first();

        if (! $patient) {
            return response()->json([
                'message' => 'No patient record found for this account. Please contact your healthcare provider.',
            ], 404);
        }

        // Step 3: Issue a 24-hour mobile access token
        $rawToken = 'pat_' . Str::random(40);

        PatientAccessToken::create([
            'patient_id'   => $patient->id,
            'token_hash'   => Hash::make($rawToken),
            'token_prefix' => substr($rawToken, 0, 12),
            'expires_at'   => Carbon::now()->addHours(24),
        ]);

        return response()->json([
            'status'       => 'authenticated',
            'access_token' => $rawToken,
            'token_type'   => 'Bearer',
            'expires_in'   => 86400,
            'patient_id'   => $patient->id,
        ], 200);
    }
}
