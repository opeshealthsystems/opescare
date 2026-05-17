<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MobilePatientController extends Controller
{
    public function getMe(Request $request)
    {
        return response()->json([
            'health_id' => 'OC-CMR-7KQ9-MP42-X8D1',
            'display_name' => 'John D.',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+237 600-000-000',
            'email' => 'john.doe@example.com',
            'dob' => '1990-04-12',
            'sex' => 'male',
            'digital_qr_reference' => 'qr_ref_opescare_johndoe_1002',
            'status' => 'active'
        ], 200);
    }

    public function getTimeline(Request $request)
    {
        return response()->json([
            'timeline' => [
                [
                    'event_id' => 'evt_1001_a',
                    'event_type' => 'encounter',
                    'facility_name' => 'St. Jude Clinical Research Hospital',
                    'occurred_at' => date('Y-m-d\TH:i:s\Z', time() - 86400),
                    'summary' => 'General Consult Outpatient visit for Fever.'
                ]
            ]
        ], 200);
    }
}
