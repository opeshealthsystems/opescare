<?php
namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\FamilyLink;
use App\Models\Patient;
use App\Services\Identity\HealthIdGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FamilyController extends Controller
{
    public function index()
    {
        $links = FamilyLink::where('guardian_user_id', Auth::id())
            ->whereIn('status', ['active', 'pending_invite'])
            ->with('dependentPatient')
            ->orderByDesc('created_at')
            ->get();

        return view('portals.patient.family.index', compact('links'));
    }

    public function addForm()
    {
        return view('portals.patient.family.add');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'date_of_birth'=> 'required|date|before:today',
            'sex'          => 'required|in:male,female,other',
            'relationship' => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level' => 'required|in:full,read_only',
        ]);

        $gen         = new HealthIdGeneratorService();
        $countryCode = Auth::user()?->patient?->country_code ?? 'CM';
        $healthId    = $gen->generate($countryCode);

        $patient = Patient::create([
            'health_id'       => $healthId,
            'first_name'      => $data['first_name'],
            'last_name'       => $data['last_name'],
            'date_of_birth'   => $data['date_of_birth'],
            'sex'             => $data['sex'],
            'identity_status' => 'provisional',
            'is_demo'         => false,
        ]);

        FamilyLink::create([
            'guardian_user_id'     => Auth::id(),
            'dependent_patient_id' => $patient->id,
            'relationship'         => $data['relationship'],
            'access_level'         => $data['access_level'],
            'status'               => 'active',
            'created_by'           => 'self_registered',
        ]);

        return redirect()->route('portals.patient.family')
            ->with('success', 'Dependent added successfully.');
    }

    public function inviteForm()
    {
        return view('portals.patient.family.invite');
    }

    public function sendInvite(Request $request)
    {
        $data = $request->validate([
            'health_id_or_email' => 'required|string|max:255',
            'relationship'       => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level'       => 'required|in:full,read_only',
        ]);

        $search  = $data['health_id_or_email'];
        $patient = Patient::where('health_id', $search)
            ->orWhere('email', $search)
            ->where('is_demo', false)
            ->first();

        if (!$patient) {
            return back()->withErrors(['health_id_or_email' => 'No patient found with that Health ID or email.']);
        }

        $existing = FamilyLink::where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patient->id)
            ->whereIn('status', ['active', 'pending_invite'])
            ->exists();

        if ($existing) {
            return back()->withErrors(['health_id_or_email' => 'A link already exists for this patient.']);
        }

        $rawToken = Str::random(64);
        $link = FamilyLink::create([
            'guardian_user_id'     => Auth::id(),
            'dependent_patient_id' => $patient->id,
            'relationship'         => $data['relationship'],
            'access_level'         => $data['access_level'],
            'status'               => 'pending_invite',
            'created_by'           => 'invite_accepted',
            'invite_token'         => hash('sha256', $rawToken),
            'invite_expires_at'    => now()->addHours(config('family.invite_ttl_hours', 48)),
        ]);

        // Notify dependent's user account if one exists
        $dependentUser = \App\Models\User::where('patient_id', $patient->id)->first();
        if ($dependentUser && class_exists(\App\Notifications\FamilyInviteNotification::class)) {
            $dependentUser->notify(new \App\Notifications\FamilyInviteNotification($link, $rawToken));
        }

        return redirect()->route('portals.patient.family')
            ->with('success', 'Invite sent. The link will be active once accepted.');
    }

    public function acceptInvite(string $token)
    {
        $link = $this->findPendingByToken($token);
        if (!$link) {
            return view('portals.patient.family.invite-accept', [
                'error' => 'This invite link is invalid or has expired.',
                'link'  => null,
                'token' => null,
            ]);
        }
        return view('portals.patient.family.invite-accept', [
            'link'  => $link,
            'error' => null,
            'token' => $token,
        ]);
    }

    public function confirmInvite(Request $request, string $token)
    {
        $link = $this->findPendingByToken($token);
        if (!$link) {
            return redirect()->route('login')
                ->with('error', 'Invite link is invalid or expired.');
        }

        $link->update([
            'status'            => 'active',
            'created_by'        => 'invite_accepted',
            'invite_token'      => null,
            'invite_expires_at' => null,
        ]);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access granted successfully.');
    }

    public function editForm(string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('guardian_user_id', Auth::id())
            ->with('dependentPatient')
            ->firstOrFail();

        return view('portals.patient.family.edit', compact('link'));
    }

    public function update(Request $request, string $id)
    {
        $link = FamilyLink::where('id', $id)
            ->where('guardian_user_id', Auth::id())
            ->firstOrFail();

        $data = $request->validate([
            'relationship'                => 'required|in:parent,grandparent,spouse,sibling,caregiver,legal_guardian,other',
            'access_level'                => 'required|in:full,read_only',
            'notification_prefs'          => 'nullable|array',
            'notification_prefs.*.portal' => 'nullable|boolean',
            'notification_prefs.*.email'  => 'nullable|boolean',
            'notification_prefs.*.sms'    => 'nullable|boolean',
        ]);

        $link->update([
            'relationship'       => $data['relationship'],
            'access_level'       => $data['access_level'],
            'notification_prefs' => $data['notification_prefs'] ?? [],
        ]);

        return redirect()->route('portals.patient.family')
            ->with('success', 'Family link updated.');
    }

    public function revoke(string $id)
    {
        $link = FamilyLink::where('id', $id)->first();
        abort_if(!$link, 404);
        abort_if($link->guardian_user_id !== Auth::id(), 403);

        $link->update(['status' => 'revoked']);
        session()->forget('guardian_viewing_patient_id');

        return redirect()->route('portals.patient.family')
            ->with('success', 'Guardian access revoked.');
    }

    public function switchTo(string $patientId)
    {
        $link = FamilyLink::active()
            ->where('guardian_user_id', Auth::id())
            ->where('dependent_patient_id', $patientId)
            ->first();

        abort_if(!$link, 403);

        session(['guardian_viewing_patient_id' => $patientId]);

        return redirect()->route('portals.patient.appointments');
    }

    public function switchBack()
    {
        session()->forget('guardian_viewing_patient_id');
        return redirect()->route('portals.patient');
    }

    public function guardianConsentApprove(string $id)
    {
        // Dependent approves continued guardian access after age transition
        $myPatientId = Auth::user()?->patient_id;
        $link = FamilyLink::where('id', $id)
            ->where('dependent_patient_id', $myPatientId)
            ->firstOrFail();

        $link->update(['age_transition_expires_at' => null]);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access re-granted.');
    }

    public function guardianConsentDeny(string $id)
    {
        $myPatientId = Auth::user()?->patient_id;
        $link = FamilyLink::where('id', $id)
            ->where('dependent_patient_id', $myPatientId)
            ->firstOrFail();

        $link->update(['status' => 'revoked']);

        return redirect()->route('portals.patient')
            ->with('success', 'Guardian access removed.');
    }

    private function findPendingByToken(string $rawToken): ?FamilyLink
    {
        $hashed = hash('sha256', $rawToken);
        return FamilyLink::where('invite_token', $hashed)
            ->where('status', 'pending_invite')
            ->where('invite_expires_at', '>', now())
            ->with('dependentPatient', 'guardianUser')
            ->first();
    }
}
