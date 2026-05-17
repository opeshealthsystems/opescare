<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;

class StaffPortalController extends Controller
{
    public function index(Request $request)
    {
        return view('portals.staff.index');
    }
}
