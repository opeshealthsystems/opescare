<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\OpesCareErrorCode;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $phone = $request->input('phone');
        $pinHash = $request->input('pin_hash');

        if (!$phone || !$pinHash) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing phone or pin_hash.'
            ], 400);
        }

        // Mock login pass
        return response()->json([
            'status' => 'pending_2fa',
            'message' => 'Credentials verified. 2FA verification code dispatched to device.'
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $otp = $request->input('otp_code');

        if (!$otp) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing otp_code.'
            ], 400);
        }

        // Mock verification pass
        return response()->json([
            'status' => 'authenticated',
            'access_token' => 'mobile_jwt_token_stub_' . bin2hex(random_bytes(16)),
            'token_type' => 'Bearer',
            'expires_in' => 7200 // 2 hours
        ], 200);
    }

    public function registerDevice(Request $request)
    {
        $deviceUuid = $request->input('device_uuid');
        $deviceName = $request->input('device_name');

        if (!$deviceUuid || !$deviceName) {
            return response()->json([
                'status' => 'rejected',
                'error_code' => OpesCareErrorCode::VALIDATION_FAILED->value,
                'message' => 'Missing device_uuid or device_name.'
            ], 400);
        }

        return response()->json([
            'status' => 'registered',
            'device_id' => 'dev_' . bin2hex(random_bytes(8)),
            'message' => 'Biometric device paired successfully.'
        ], 201);
    }
}
