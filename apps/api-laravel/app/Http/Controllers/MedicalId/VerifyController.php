<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    /**
     * Show the Health ID manual lookup form.
     */
    public function healthId()
    {
        return view('verify.health_id', [
            'result' => null,
            'error'  => null,
        ]);
    }

    /**
     * Handle Health ID lookup POST.
     */
    public function healthIdLookup(Request $request)
    {
        $request->validate([
            'health_id' => 'required|string|max:64',
            'purpose'   => 'required|string|max:60',
        ]);

        // TODO: implement real lookup via MedicalIdService
        // Stub: return a not-found result so the page renders correctly.
        return view('verify.health_id', [
            'result' => null,
            'error'  => __('verify.error_not_found', [], app()->getLocale()) ?: 'No verified Health ID found for the provided identifier. Please check the ID and try again.',
        ]);
    }

    /**
     * Handle QR token verification (scanned link).
     */
    public function qr(string $token)
    {
        // TODO: implement real token validation via MedicalIdService
        return view('verify.qr', [
            'token'  => $token,
            'result' => null, // stub – replace with real lookup
        ]);
    }
}
