<?php

namespace App\Http\Controllers\Demo;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DemoAccessController extends Controller
{
    public function loginAs(Request $request)
    {
        if (!config('demo.enabled')) {
            abort(403, 'Demo mode disabled.');
        }

        $request->validate([
            'role'  => 'required|string',
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->where('is_demo', true)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Demo account not found. Please run the demo seeder.']);
        }

        Auth::login($user);

        $mode     = $request->input('mode', 'public');
        $lifetime = config("demo.session.{$mode}_lifetime_minutes", 30);

        session([
            'demo_mode_type'          => $mode,
            'demo_session_expires_at' => now()->addMinutes($lifetime),
            'demo_role'               => $request->role,
        ]);

        Log::channel('single')->info('demo_login_completed', [
            'demo_role'          => $request->role,
            'demo_user_id'       => $user->id,
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
            'session_expires_at' => now()->addMinutes($lifetime),
        ]);

        return redirect($this->portalForRole($request->role));
    }

    private function portalForRole(string $role): string
    {
        return match (true) {
            in_array($role, ['doctor', 'multi_doctor', 'nurse', 'specialist', 'pharmacist', 'labtech']) => '/portals/staff',
            in_array($role, ['facility_admin', 'facility_ceo', 'finance'])                             => '/portals/admin',
            $role === 'insurance_claims'                                                                => '/portals/insurance/claims',
            $role === 'insurance_preauth'                                                               => '/portals/insurance/preauths',
            in_array($role, ['patient', 'guardian'])                                                   => '/portals/patient',
            $role === 'platform_admin'                                                                  => '/portals/admin/cc',
            default                                                                                     => '/portals/staff',
        };
    }
}
