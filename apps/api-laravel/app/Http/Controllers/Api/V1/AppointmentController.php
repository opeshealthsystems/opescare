<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Modules\Appointments\Services\AppointmentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::query()->orderBy('scheduled_at');

        if ($request->query('scope') === 'patient') {
            abort_unless($request->filled('patient_id'), 403, 'PATIENT_SCOPE_REQUIRES_PATIENT_ID');
            $query->where('patient_id', $request->query('patient_id'));
        } elseif ($request->filled('patient_id')) {
            $query->where('patient_id', $request->query('patient_id'));
        }

        if ($request->filled('facility_id')) {
            $query->where('facility_id', $request->query('facility_id'));
        }

        if ($request->filled('provider_id')) {
            $query->where('provider_id', $request->query('provider_id'));
        }

        return response()->json(['data' => $query->get()->map(fn (Appointment $appointment) => $this->serialize($appointment))->values()]);
    }

    public function store(Request $request, AppointmentService $service)
    {
        $appointment = $service->book($request->validate([
            'patient_id' => ['required', 'uuid'],
            'facility_id' => ['required', 'uuid'],
            'provider_id' => ['nullable', 'uuid'],
            'appointment_slot_id' => ['nullable', 'uuid'],
            'scheduled_at' => ['nullable', 'date'],
            'appointment_type' => ['required', 'string'],
            'booked_by_type' => ['nullable', 'string'],
            'booked_by_id' => ['nullable', 'uuid'],
            'reason' => ['nullable', 'string'],
        ]));

        return response()->json(['data' => $this->serialize($appointment)], 201);
    }

    public function reschedule(Request $request, Appointment $appointment, AppointmentService $service)
    {
        $rescheduled = $service->reschedule($appointment, $request->validate([
            'appointment_slot_id' => ['nullable', 'uuid'],
            'scheduled_at' => ['nullable', 'date'],
            'reason' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]));

        return response()->json(['data' => $this->serialize($rescheduled)]);
    }

    public function cancel(Request $request, Appointment $appointment, AppointmentService $service)
    {
        $validated = $request->validate([
            'reason' => ['required', 'string'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        return response()->json(['data' => $this->serialize($service->cancel($appointment, $validated['reason'], $validated['actor_id'] ?? null))]);
    }

    public function checkIn(Request $request, Appointment $appointment, AppointmentService $service)
    {
        $validated = $request->validate(['actor_id' => ['nullable', 'uuid']]);

        return response()->json(['data' => $this->serialize($service->checkIn($appointment, $validated['actor_id'] ?? null))]);
    }

    public function noShow(Request $request, AppointmentService $service)
    {
        $validated = $request->validate([
            'as_of' => ['nullable', 'date'],
            'actor_id' => ['nullable', 'uuid'],
        ]);

        $count = $service->markNoShows(isset($validated['as_of']) ? Carbon::parse($validated['as_of']) : now(), $validated['actor_id'] ?? null);

        return response()->json(['status' => 'ok', 'marked_no_show' => $count]);
    }

    private function serialize(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'facility_id' => $appointment->facility_id,
            'provider_id' => $appointment->provider_id,
            'appointment_slot_id' => $appointment->appointment_slot_id,
            'visit_id' => $appointment->visit_id,
            'rescheduled_from_appointment_id' => $appointment->rescheduled_from_appointment_id,
            'appointment_type' => $appointment->appointment_type,
            'status' => $appointment->status,
            'scheduled_at' => $appointment->scheduled_at?->toISOString(),
            'billing_deferred' => $appointment->billing_deferred,
            'telemedicine_deferred' => $appointment->telemedicine_deferred,
        ];
    }
}
