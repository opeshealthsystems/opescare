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
            'phone_number' => 'required|string',
            'pin'          => 'required|string|min:4|max:8',
        ]);

        $patient = Patient::where('phone_number', $request->phone_number)->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient not found.'], 404);
        }

        // Bootstrap: set PIN on first login if none stored yet
        if (is_null($patient->pin_hash)) {
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
        } catch (\Throwable) {
            // SMS failure is non-fatal in development; OTP is stored regardless
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

        $patient = Patient::where('phone_number', $request->phone_number)->first();

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
            'patient_id' => $patient->id,
            'token_hash' => Hash::make($rawToken),
            'expires_at' => Carbon::now()->addHours(24),
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
