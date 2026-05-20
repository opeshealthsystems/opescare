<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Modules\Staff\Services\StaffService;
use App\Modules\Staff\Services\RosterService;
use App\Modules\Staff\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * StaffController — Staff, HR & Shift Management API.
 *
 * Covers staff profiles, credentials, duty rosters, shift assignments,
 * leave requests, and professional license management.
 */
class StaffController extends Controller
{
    public function __construct(
        private StaffService  $staff,
        private RosterService $roster,
        private LeaveService  $leave
    ) {}

    // ── Staff Profiles ─────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->staff->listForFacility($request->input('facility_id'), $request->all())
        );
    }

    public function show(string $staffId): JsonResponse
    {
        return response()->json($this->staff->get($staffId));
    }

    public function updateProfile(Request $request, string $staffId): JsonResponse
    {
        $validated = $request->validate([
            'specialization'  => ['nullable', 'string'],
            'bio'             => ['nullable', 'string'],
            'contact_phone'   => ['nullable', 'string'],
        ]);

        return response()->json($this->staff->updateProfile($staffId, $validated, $request->user()->id));
    }

    // ── Duty Roster ────────────────────────────────────────────────────────

    public function getRoster(Request $request): JsonResponse
    {
        return response()->json(
            $this->roster->getRoster(
                $request->input('facility_id'),
                $request->input('department_id'),
                $request->input('from'),
                $request->input('to')
            )
        );
    }

    public function assignShift(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'staff_id'    => ['required', 'uuid'],
            'facility_id' => ['required', 'uuid'],
            'shift_date'  => ['required', 'date'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time'],
            'role'        => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->roster->assignShift($validated, $request->user()->id),
            201
        );
    }

    public function removeShift(Request $request, string $shiftId): JsonResponse
    {
        $this->roster->removeShift($shiftId, $request->user()->id);
        return response()->json(['message' => 'Shift removed.']);
    }

    // ── Leave Requests ─────────────────────────────────────────────────────

    public function requestLeave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'leave_type' => ['required', 'in:annual,sick,maternity,paternity,compassionate,unpaid'],
            'from_date'  => ['required', 'date'],
            'to_date'    => ['required', 'date', 'after_or_equal:from_date'],
            'reason'     => ['nullable', 'string'],
        ]);

        return response()->json(
            $this->leave->request($request->user()->id, $validated),
            201
        );
    }

    public function approveLeave(Request $request, string $leaveId): JsonResponse
    {
        return response()->json(
            $this->leave->approve($leaveId, $request->user()->id, $request->input('notes'))
        );
    }

    public function rejectLeave(Request $request, string $leaveId): JsonResponse
    {
        return response()->json(
            $this->leave->reject($leaveId, $request->user()->id, $request->input('reason'))
        );
    }
}
