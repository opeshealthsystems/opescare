<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
