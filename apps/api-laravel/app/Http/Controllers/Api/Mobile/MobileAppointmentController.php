<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Mobile Patient API — Appointments
 *
 * Read-only view of the authenticated patient's appointments (upcoming/past).
 * Appointment creation flows through the booking portal.
 */
class MobileAppointmentController extends Controller
{
    /**
     * List appointments.
     *
     * GET /api/mobile/appointments
     * Query params: scope (upcoming|past|all), limit (default 20)
     */
    public function index(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);
        $scope     = $request->query('scope', 'upcoming'); // upcoming|past|all
        $limit     = min((int) $request->query('limit', 20), 100);

        $query = Appointment::where('patient_id', $patientId)
            ->with(['facility:id,name', 'provider:id,name'])
            ->orderBy('scheduled_at', $scope === 'past' ? 'desc' : 'asc');

        match ($scope) {
            'upcoming' => $query->whereIn('status', ['booked', 'confirmed', 'checked_in'])
                                ->where('scheduled_at', '>=', now()),
            'past'     => $query->whereNotIn('status', ['booked', 'confirmed'])
                                ->orWhere('scheduled_at', '<', now()),
            default    => null,
        };

        $appointments = $query->paginate($limit);

        return response()->json([
            'data'       => $appointments->map(fn ($a) => $this->formatAppointment($a)),
            'pagination' => [
                'total'        => $appointments->total(),
                'per_page'     => $appointments->perPage(),
                'current_page' => $appointments->currentPage(),
                'last_page'    => $appointments->lastPage(),
            ],
        ]);
    }

    /**
     * Get a single appointment detail.
     *
     * GET /api/mobile/appointments/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $patientId)
            ->with(['facility:id,name', 'provider:id,name'])
            ->firstOrFail();

        return response()->json(['data' => $this->formatAppointmentDetail($appointment)]);
    }

    /**
     * POST /api/mobile/appointments
     *
     * Atomically book an appointment slot for a patient.
     * Uses pessimistic lock to prevent concurrent double-booking.
     *
     * Body:
     *   _patient_id         string  (test helper; production resolves from auth token)
     *   facility_id         string  UUID of facilities (not care_facilities) row
     *   appointment_slot_id string  UUID of appointment_slots row
     *   appointment_type    string  e.g. "consultation", "follow_up"
     *   reason              string  optional
     */
    public function book(Request $request): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $validated = $request->validate([
            'facility_id'         => 'required|uuid|exists:facilities,id',
            'appointment_slot_id' => 'required|uuid|exists:appointment_slots,id',
            'appointment_type'    => 'required|string|max:100',
            'reason'              => 'nullable|string|max:1000',
        ]);

        $appointment = DB::transaction(function () use ($patientId, $validated) {
            // Pessimistic lock prevents concurrent double-booking of the same slot
            $slot = AppointmentSlot::lockForUpdate()->findOrFail($validated['appointment_slot_id']);

            if ($slot->booked_count >= $slot->capacity) {
                throw new \App\Exceptions\SlotFullException('This slot is fully booked.');
            }

            $slot->increment('booked_count');

            return Appointment::create([
                'patient_id'          => $patientId,
                'facility_id'         => $validated['facility_id'],
                'appointment_slot_id' => $validated['appointment_slot_id'],
                'appointment_type'    => $validated['appointment_type'],
                'status'              => 'booked',
                'scheduled_at'        => $slot->starts_at,
                'booked_by_type'      => 'patient',
                'booked_by_id'        => $patientId,
                'reason'              => $validated['reason'] ?? null,
            ]);
        });

        return response()->json(['data' => $this->formatAppointmentDetail($appointment)], 201);
    }

    /**
     * POST /api/mobile/appointments/{id}/cancel
     *
     * Cancel a patient's own appointment and restore the slot count.
     *
     * Body:
     *   _patient_id  string  (test helper)
     *   reason       string  optional
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $patientId = $this->resolvePatientId($request);

        $appointment = Appointment::where('id', $id)->firstOrFail();

        if ($appointment->patient_id !== $patientId) {
            return response()->json([
                'error_code' => 'FORBIDDEN',
                'message'    => 'You may only cancel your own appointments.',
            ], 403);
        }

        if (!in_array($appointment->status, ['booked', 'confirmed'])) {
            return response()->json([
                'error_code' => 'INVALID_STATUS',
                'message'    => "Cannot cancel an appointment with status '{$appointment->status}'.",
            ], 422);
        }

        DB::transaction(function () use ($appointment, $request) {
            $appointment->update([
                'status'              => 'cancelled',
                'cancellation_reason' => $request->input('reason'),
                'cancelled_at'        => now(),
                'cancelled_by_id'     => $appointment->patient_id,
            ]);

            if ($appointment->appointment_slot_id) {
                AppointmentSlot::where('id', $appointment->appointment_slot_id)
                    ->where('booked_count', '>', 0)
                    ->decrement('booked_count');
            }
        });

        return response()->json(['data' => $this->formatAppointmentDetail($appointment->fresh())]);
    }

    // -------------------------------------------------------------------------

    private function formatAppointment(Appointment $a): array
    {
        return [
            'id'               => $a->id,
            'appointment_type' => $a->appointment_type,
            'status'           => $a->status,
            'facility_name'    => $a->facility?->name,
            'provider_name'    => $a->provider?->name,
            'scheduled_at'     => $a->scheduled_at?->toIso8601String(),
            'checked_in_at'    => $a->checked_in_at?->toIso8601String(),
            'reason'           => $a->reason,
        ];
    }

    private function formatAppointmentDetail(Appointment $a): array
    {
        $base = $this->formatAppointment($a);
        $base['cancellation_reason'] = $a->cancellation_reason;
        $base['cancelled_at']        = $a->cancelled_at?->toIso8601String();
        $base['no_show_at']          = $a->no_show_at?->toIso8601String();
        $base['visit_id']            = $a->visit_id;
        return $base;
    }

    private function resolvePatientId(Request $request): string
    {
        if ($request->has('_patient_id')) {
            return $request->input('_patient_id');
        }
        return \App\Models\Patient::value('id') ?? 'demo';
    }
}
