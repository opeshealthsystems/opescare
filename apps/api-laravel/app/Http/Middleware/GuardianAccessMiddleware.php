<?php
namespace App\Http\Middleware;

use App\Models\FamilyLink;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianAccessMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $patientId = session('guardian_viewing_patient_id');

        if (!$patientId) {
            return $next($request);
        }

        $link = FamilyLink::where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patientId)
            ->where('status', 'active')
            ->with('dependentPatient')
            ->first();

        if (!$link || $link->isExpiredByAge()) {
            session()->forget('guardian_viewing_patient_id');
            return redirect()->route('portals.patient')
                ->with('error', 'Guardian access is no longer active for this dependent.');
        }

        $request->attributes->set('guardian_link', $link);
        $request->attributes->set('viewing_patient', $link->dependentPatient);

        return $next($request);
    }
}
