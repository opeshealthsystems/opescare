<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Facility;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\QueueTicket;
use App\Models\SupportTicket;
use App\Modules\Appointments\Services\AppointmentService;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\PaymentService;
use App\Modules\Queue\Services\QueueService;
use App\Modules\Support\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class StaffPortalController extends Controller
{
    // ─── Demo context helpers ─────────────────────────────────────
    // TODO: Replace with auth()->user() context when real auth is wired.

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    private function demoFacilityId(): ?string
    {
        return Facility::value('id');
    }

    // ─── Dashboard ────────────────────────────────────────────────

    public function index(Request $request)
    {
        $facilityId = $this->demoFacilityId();

        $todayStart = now()->startOfDay();
        $todayEnd   = now()->endOfDay();

        $kpis = [
            'todays_appointments' => Appointment::whereBetween('scheduled_at', [$todayStart, $todayEnd])->count(),
            'in_queue'            => QueueTicket::whereIn('status', ['waiting', 'called', 'service_started'])->count(),
            'pending_referrals'   => DB::table('referral_cases')->where('status', 'draft')->count(),
            'open_invoices'       => Invoice::where('status', 'issued')->count(),
        ];

        return view('portals.staff.index', compact('kpis'));
    }

    // ─── Appointments ─────────────────────────────────────────────

    public function appointments(Request $request)
    {
        $query = Appointment::query()->orderByDesc('scheduled_at');

        if ($request->filled('patient_id')) {
            $query->where('patient_id', 'like', '%'.$request->patient_id.'%');
        }
        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->provider_id);
        }
        if ($request->filled('date')) {
            $query->whereDate('scheduled_at', $request->date);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->limit(100)->get();

        return view('portals.staff.appointments', compact('appointments'));
    }

    public function appointmentsCreate(Request $request)
    {
        $facilities = Facility::orderBy('name')->limit(20)->get();
        $patients   = Patient::whereNotNull('health_id')->orderBy('created_at', 'desc')->limit(20)->get();

        return view('portals.staff.appointments_create', compact('facilities', 'patients'));
    }

    public function appointmentsStore(Request $request, AppointmentService $svc)
    {
        $request->validate([
            'patient_id'       => 'required|string',
            'facility_id'      => 'required|string',
            'appointment_type' => 'required|string',
            'scheduled_at'     => 'required|date',
            'reason'           => 'nullable|string|max:500',
        ]);

        try {
            // Bypass availability check for demo (no schedules seeded yet)
            $appointment = Appointment::create([
                'patient_id'       => $request->patient_id,
                'facility_id'      => $request->facility_id,
                'appointment_type' => $request->appointment_type,
                'status'           => 'scheduled',
                'scheduled_at'     => $request->scheduled_at,
                'booked_by_type'   => 'staff',
                'booked_by_id'     => $this->demoActorId(),
                'reason'           => $request->reason,
                'billing_deferred'     => true,
                'telemedicine_deferred' => true,
            ]);

            return redirect()->route('portals.staff.appointments')
                ->with('success', __('public.staff_portal.appointment_booked', [], app()->getLocale()) ?: 'Appointment booked successfully.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function appointmentsConfirm(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update(['status' => 'confirmed']);

        return redirect()->route('portals.staff.appointments')
            ->with('success', __('public.staff_portal.appointment_confirmed', [], app()->getLocale()) ?: 'Appointment confirmed.');
    }

    public function appointmentsCancel(Request $request, string $id, AppointmentService $svc)
    {
        $request->validate(['reason' => 'required|string|min:5|max:500']);

        try {
            $appointment = Appointment::findOrFail($id);
            $svc->cancel($appointment, $request->reason, $this->demoActorId());

            return redirect()->route('portals.staff.appointments')
                ->with('success', __('public.staff_portal.appointment_cancelled', [], app()->getLocale()) ?: 'Appointment cancelled.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function appointmentsCheckIn(Request $request, string $id, AppointmentService $svc)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $svc->checkIn($appointment, $this->demoActorId());

            return redirect()->route('portals.staff.appointments')
                ->with('success', __('public.staff_portal.appointment_checked_in', [], app()->getLocale()) ?: 'Patient checked in.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function appointmentsNoShow(Request $request, string $id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->update([
            'status'     => 'no_show',
            'no_show_at' => now(),
        ]);

        return redirect()->route('portals.staff.appointments')
            ->with('success', __('public.staff_portal.appointment_no_show', [], app()->getLocale()) ?: 'Appointment marked as no-show.');
    }

    // ─── Queue ────────────────────────────────────────────────────

    public function queue(Request $request)
    {
        $query = QueueTicket::query()
            ->orderBy('priority_level')
            ->orderBy('checked_in_at');

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->facility_id);
        }
        if ($request->filled('queue_name')) {
            $query->where('current_queue', $request->queue_name);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['waiting', 'called', 'service_started']);
        }

        $entries = $query->limit(100)->get();

        return view('portals.staff.queue', compact('entries'));
    }

    public function queueDisplay(Request $request)
    {
        $facilityId = $request->query('facility_id');
        $tickets = QueueTicket::whereIn('status', ['waiting', 'called', 'service_started'])
            ->when($facilityId, fn ($q) => $q->where('facility_id', $facilityId))
            ->orderBy('priority_level')
            ->orderBy('checked_in_at')
            ->limit(20)
            ->get();

        return view('portals.staff.queue_display', compact('tickets'));
    }

    public function queueCheckIn(Request $request, QueueService $svc)
    {
        $request->validate([
            'patient_id'        => 'required|string',
            'destination_queue' => 'required|string',
        ]);

        $facilityId = $this->demoFacilityId() ?? $request->input('facility_id', 'demo-facility');

        try {
            $svc->checkInWalkIn([
                'patient_id'        => $request->patient_id,
                'facility_id'       => $facilityId,
                'destination_queue' => $request->destination_queue,
                'visit_type'        => 'outpatient',
                'actor_id'          => $this->demoActorId(),
                'check_in_type'     => 'walk_in',
            ]);

            return redirect()->route('portals.staff.queue')
                ->with('success', __('public.staff_portal.queue_checked_in', [], app()->getLocale()) ?: 'Patient added to queue.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function queueCall(Request $request, string $id, QueueService $svc)
    {
        try {
            $ticket = QueueTicket::findOrFail($id);
            $ticket->update([
                'status'    => 'called',
                'called_at' => now(),
            ]);

            return redirect()->route('portals.staff.queue')
                ->with('success', __('public.staff_portal.queue_called', [], app()->getLocale()) ?: 'Patient called.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function queueStart(Request $request, string $id, QueueService $svc)
    {
        try {
            $ticket = QueueTicket::findOrFail($id);
            $svc->startService($ticket, $this->demoActorId());

            return redirect()->route('portals.staff.queue')
                ->with('success', __('public.staff_portal.queue_service_started', [], app()->getLocale()) ?: 'Service started.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function queueComplete(Request $request, string $id, QueueService $svc)
    {
        $request->validate(['reason' => 'nullable|string|max:300']);

        try {
            $ticket = QueueTicket::findOrFail($id);
            $svc->complete($ticket, $request->reason ?: 'Completed by staff.', $this->demoActorId());

            return redirect()->route('portals.staff.queue')
                ->with('success', __('public.staff_portal.queue_completed', [], app()->getLocale()) ?: 'Queue ticket completed.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─── Billing ─────────────────────────────────────────────────

    public function billing(Request $request)
    {
        $query = Invoice::query()->orderByDesc('issued_at');

        if ($request->filled('patient_id')) {
            $query->where('patient_id', 'like', '%'.$request->patient_id.'%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->limit(100)->get();

        return view('portals.staff.billing', compact('invoices'));
    }

    public function billingCreate(Request $request)
    {
        $patients = Patient::whereNotNull('health_id')->orderBy('created_at', 'desc')->limit(20)->get();

        return view('portals.staff.billing_create', compact('patients'));
    }

    public function billingStore(Request $request, BillingService $svc)
    {
        $request->validate([
            'patient_id'        => 'required|string',
            'items'             => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity'  => 'required|numeric|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $facilityId = $this->demoFacilityId() ?? $request->input('facility_id', 'demo-facility');

        try {
            $invoice = $svc->createInvoice([
                'patient_id'  => $request->patient_id,
                'facility_id' => $facilityId,
                'items'       => $request->items,
                'actor_id'    => $this->demoActorId(),
            ]);

            return redirect()->route('portals.staff.billing')
                ->with('success', (__('public.staff_portal.invoice_created', [], app()->getLocale()) ?: 'Invoice created:').' '.$invoice->invoice_number);
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function billingPay(Request $request, string $id, PaymentService $svc)
    {
        $request->validate([
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
        ]);

        try {
            $invoice = Invoice::findOrFail($id);
            $svc->recordPayment($invoice, [
                'amount'         => $request->amount,
                'payment_method' => $request->payment_method,
                'actor_id'       => $this->demoActorId(),
                'reference_number' => $request->reference_number,
            ]);

            return redirect()->route('portals.staff.billing')
                ->with('success', __('public.staff_portal.payment_recorded', [], app()->getLocale()) ?: 'Payment recorded successfully.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─── Support ─────────────────────────────────────────────────

    public function support(Request $request)
    {
        $query = SupportTicket::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $tickets = $query->limit(100)->get();

        return view('portals.staff.support', compact('tickets'));
    }

    public function supportStore(Request $request, SupportService $svc)
    {
        $request->validate([
            'subject'  => 'required|string|max:200',
            'category' => 'required|string',
            'priority' => 'required|in:normal,high,urgent,critical',
            'description' => 'required|string|min:10|max:2000',
        ]);

        try {
            $svc->createTicket([
                'requester_type' => 'staff',
                'requester_id'   => $this->demoActorId(),
                'facility_id'    => $this->demoFacilityId(),
                'category'       => $request->category,
                'priority'       => $request->priority,
                'subject'        => $request->subject,
                'description'    => $request->description,
            ], $this->demoActorId());

            return redirect()->route('portals.staff.support')
                ->with('success', __('public.staff_portal.ticket_created', [], app()->getLocale()) ?: 'Support ticket created.');
        } catch (Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function supportReply(Request $request, string $id, SupportService $svc)
    {
        $request->validate(['body' => 'required|string|min:2|max:2000']);

        try {
            $ticket = SupportTicket::findOrFail($id);
            $svc->addMessage($ticket, [
                'sender_type' => 'staff',
                'sender_id'   => $this->demoActorId(),
                'body'        => $request->body,
                'internal'    => false,
            ], $this->demoActorId());

            return redirect()->route('portals.staff.support')
                ->with('success', __('public.staff_portal.ticket_reply_sent', [], app()->getLocale()) ?: 'Reply sent.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function supportClose(Request $request, string $id, SupportService $svc)
    {
        try {
            $ticket = SupportTicket::findOrFail($id);
            $svc->resolveTicket($ticket, $this->demoActorId(), $request->resolution_note);

            return redirect()->route('portals.staff.support')
                ->with('success', __('public.staff_portal.ticket_closed', [], app()->getLocale()) ?: 'Ticket resolved.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // ─── Referrals ────────────────────────────────────────────────

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
        return redirect()->route('portals.staff.referrals.show', $id)->with('success', 'Referral sent.');
    }

    public function referralsAccept(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)->with('success', 'Referral accepted.');
    }

    public function referralsReject(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)->with('success', 'Referral rejected.');
    }

    public function referralsComplete(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals.show', $id)->with('success', 'Referral marked as completed.');
    }

    public function referralsCancel(Request $request, $id)
    {
        return redirect()->route('portals.staff.referrals')->with('success', 'Referral cancelled.');
    }

    // ─── Immunizations ────────────────────────────────────────────

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

        return redirect()->route('portals.staff.immunizations')->with('success', 'Immunization record saved.');
    }
}
