<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffPortalController extends Controller
{
    public function index(Request $request)
    {
        return view('portals.staff.index');
    }

    public function appointments(Request $request)
    {
        return view('portals.staff.appointments', [
            'appointments' => collect([]),
        ]);
    }

    public function queue(Request $request)
    {
        return view('portals.staff.queue', [
            'entries' => collect([]),
        ]);
    }

    public function queueDisplay(Request $request)
    {
        // Standalone TV/kiosk queue board — no layout, no auth required by default
        // In production this would fetch live queue data from the queue service
        $facilityId = $request->query('facility_id');
        return view('portals.staff.queue_display', [
            'tickets' => [],
        ]);
    }

    public function billing(Request $request)
    {
        return view('portals.staff.billing', [
            'invoices' => collect([]),
        ]);
    }

    public function support(Request $request)
    {
        return view('portals.staff.support', [
            'tickets' => collect([]),
        ]);
    }

    public function referrals(Request $request)
    {
        return view('portals.staff.referrals.index', [
            'referrals' => collect([]),
        ]);
    }

    public function referralsCreate(Request $request)
    {
        return view('portals.staff.referrals.create');
    }

    public function referralsStore(Request $request)
    {
        $request->validate([
            'patient_id'            => 'required|string',
            'urgency'               => 'required|in:routine,urgent,emergency',
            'referring_facility_id' => 'required|string',
            'reason'                => 'required|string|min:10',
        ]);

        return redirect()->route('portals.staff.referrals')
            ->with('success', 'Referral draft created successfully.');
    }

    public function referralsShow(Request $request, $id)
    {
        $referral = (object) [
            'id'                    => $id,
            'patient_id'            => 'DEMO-PATIENT-ID',
            'status'                => 'draft',
            'priority'              => 'routine',
            'urgency'               => 'routine',
            'referring_facility_id' => null,
            'receiving_facility_id' => null,
            'specialty'             => null,
            'reason'                => 'Demo referral',
            'clinical_summary'      => null,
            'expires_at'            => null,
            'created_at'            => now(),
            'updated_at'            => now(),
        ];

        return view('portals.staff.referrals.show', compact('referral'));
    }

    public function referralsSend(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)
            ->with('success', 'Referral sent.');
    }

    public function referralsAccept(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)
            ->with('success', 'Referral accepted.');
    }

    public function referralsReject(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)
            ->with('success', 'Referral rejected.');
    }

    public function referralsComplete(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)
            ->with('success', 'Referral marked as completed.');
    }

    public function referralsCancel(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals')
            ->with('success', 'Referral cancelled.');
    }

    public function immunizations(Request $request)
    {
        return view('portals.staff.immunizations.index', [
            'records'  => collect([]),
            'schedule' => collect([]),
        ]);
    }

    public function immunizationsRecord(Request $request)
    {
        return view('portals.staff.immunizations.record');
    }

    public function immunizationsStore(Request $request)
    {
        $request->validate([
            'patient_id'      => 'required|string',
            'facility_id'     => 'required|string',
            'vaccine_code'    => 'required|string',
            'vaccine_name'    => 'required|string',
            'administered_at' => 'required|date',
            'status'          => 'required|in:completed,not_done',
        ]);

        return redirect()->route('portals.staff.immunizations')
            ->with('success', 'Immunization record saved.');
    }
}
