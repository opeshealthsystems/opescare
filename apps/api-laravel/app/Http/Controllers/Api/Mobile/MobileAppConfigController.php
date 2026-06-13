<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class MobileAppConfigController extends Controller
{
    /**
     * GET /api/mobile/app-config
     *
     * Public, unauthenticated version-gate consumed by the patient app at
     * startup (before login) to drive the forced-update flow.
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'min_supported_build' => (int) config('mobile.min_supported_build'),
            'latest_version'      => (string) config('mobile.latest_version'),
            'store_url'           => (string) config('mobile.store_url'),
        ]);
    }
}
