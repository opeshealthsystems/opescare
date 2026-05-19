<?php

namespace App\Http\Controllers\MedicalId;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\StaffProfile;
use App\Models\StaffShift;
use App\Models\DutyRoster;
use App\Modules\Staff\Services\StaffService;
use App\Modules\Staff\Services\RosterService;
use App\Modules\Staff\Services\LeaveService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffHRPortalController extends Controller
{
    public function __construct(
        private StaffService  $staffService,
        private RosterService $rosterService,
        private LeaveService  $leaveService,
    ) {}

    // ── Helpers ──────────────────────────────────────────────

    private function demoActorId(): string
    {
        return session('auth_email') ?: 'demo-staff';
    }

    private function demoFacilityId(): string
    {
        return Facility::value('id') ?? '';
    }

    // ── Staff Directory ───────────────────────────────────────

    public function directory(Request $request): View
    {
        $facilityId = $this->demoFacilityId();

        $staff = $this->staffService->listStaff($facilityId, $request->only([
            'status', 'department', 'staff_category', 'search',
        ]));

        $departments = StaffProfile::where('facility_id', $facilityId)
            ->whereNotNull('department')
            ->distinct()->pluck('department')->sort()->values();

        return view('portals.staff.hr.directory', compact('staff', 'departments'));
    }

    public function directoryStore(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name'       => 'required|string|max:100',
            'last_name'        => 'required|string|max:100',
            'staff_category'   => 'required|string',
            'employment_type'  => 'required|string',
        ]);

        $facilityId = $this->demoFacilityId();
        $data = $request->except(['_token']);

        if (empty($data['employee_number'])) {
            $data['employee_number'] = $this->staffService->generateEmployeeNumber($facilityId);
        }

        $this->staffService->createStaffProfile($facilityId, $data);

        return redirect()->route('portals.staff.hr.directory')
            ->with('success', 'Staff member added successfully.');
    }

    public function directoryStatus(Request $request, string $id): RedirectResponse
    {
        $request->validate(['status' => 'required|string']);
        try {
            $this->staffService->updateStaffStatus($id, $request->input('status'));
            return redirect()->route('portals.staff.hr.directory')
                ->with('success', 'Staff status updated.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.directory')
                ->with('error', $e->getMessage());
        }
    }

    public function addLicense(Request $request, string $id): RedirectResponse
    {
        $request->validate([
            'profession'    => 'required|string',
            'license_number'=> 'required|string|max:100',
            'issuing_body'  => 'required|string|max:200',
        ]);

        $this->staffService->addLicense($id, $request->except(['_token']));

        return redirect()->route('portals.staff.hr.directory')
            ->with('success', 'License added.');
    }

    // ── Shifts ────────────────────────────────────────────────

    public function shifts(): View
    {
        $facilityId = $this->demoFacilityId();
        $shifts = $this->rosterService->listShifts($facilityId);
        return view('portals.staff.hr.shifts', compact('shifts'));
    }

    public function shiftsStore(Request $request): RedirectResponse
    {
        $request->validate([
            'name'       => 'required|string|max:100',
            'start_time' => 'required',
            'end_time'   => 'required',
        ]);

        $this->rosterService->createShift($this->demoFacilityId(), $request->except(['_token']));

        return redirect()->route('portals.staff.hr.shifts')
            ->with('success', 'Shift created.');
    }

    public function shiftsToggle(string $id): RedirectResponse
    {
        $this->rosterService->toggleShiftStatus($id);
        return redirect()->route('portals.staff.hr.shifts')
            ->with('success', 'Shift status updated.');
    }

    // ── Rosters ───────────────────────────────────────────────

    public function roster(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $rosters = $this->rosterService->listRosters($facilityId, $request->only(['department', 'status']));

        $staff  = StaffProfile::where('facility_id', $facilityId)
            ->where('status', 'active')
            ->orderBy('last_name')->get();
        $shifts = StaffShift::where('facility_id', $facilityId)
            ->where('status', 'active')->get();

        $departments = StaffProfile::where('facility_id', $facilityId)
            ->whereNotNull('department')
            ->distinct()->pluck('department')->sort()->values();

        return view('portals.staff.hr.roster', compact('rosters', 'staff', 'shifts', 'departments'));
    }

    public function rosterStore(Request $request): RedirectResponse
    {
        $request->validate([
            'department'   => 'required|string',
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
        ]);

        $this->rosterService->createRoster(
            $this->demoFacilityId(),
            $this->demoActorId(),
            $request->except(['_token'])
        );

        return redirect()->route('portals.staff.hr.roster')
            ->with('success', 'Roster created.');
    }

    public function rosterPublish(string $id): RedirectResponse
    {
        try {
            $this->rosterService->publishRoster($id);
            return redirect()->route('portals.staff.hr.roster')
                ->with('success', 'Roster published.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.roster')
                ->with('error', $e->getMessage());
        }
    }

    public function rosterArchive(string $id): RedirectResponse
    {
        try {
            $this->rosterService->archiveRoster($id);
            return redirect()->route('portals.staff.hr.roster')
                ->with('success', 'Roster archived.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.roster')
                ->with('error', $e->getMessage());
        }
    }

    public function rosterAssign(Request $request, string $rosterId): RedirectResponse
    {
        $request->validate([
            'staff_profile_id' => 'required|uuid',
            'staff_shift_id'   => 'required|uuid',
            'work_date'        => 'required|date',
        ]);

        try {
            $this->rosterService->addAssignment(
                $rosterId,
                $this->demoActorId(),
                $request->except(['_token'])
            );
            return redirect()->route('portals.staff.hr.roster')
                ->with('success', 'Assignment added.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.roster')
                ->with('error', $e->getMessage());
        }
    }

    public function rosterUnassign(string $assignmentId): RedirectResponse
    {
        $this->rosterService->removeAssignment($assignmentId);
        return redirect()->route('portals.staff.hr.roster')
            ->with('success', 'Assignment removed.');
    }

    // ── Leave ─────────────────────────────────────────────────

    public function leave(Request $request): View
    {
        $facilityId = $this->demoFacilityId();
        $requests   = $this->leaveService->listLeaveRequests($facilityId, $request->only(['status', 'leave_type']));

        $staff = StaffProfile::where('facility_id', $facilityId)
            ->where('status', 'active')
            ->orderBy('last_name')->get();

        return view('portals.staff.hr.leave', compact('requests', 'staff'));
    }

    public function leaveStore(Request $request): RedirectResponse
    {
        $request->validate([
            'staff_profile_id' => 'required|uuid',
            'leave_type'       => 'required|string',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
        ]);

        $this->leaveService->requestLeave(
            $request->input('staff_profile_id'),
            $request->except(['_token', 'staff_profile_id'])
        );

        return redirect()->route('portals.staff.hr.leave')
            ->with('success', 'Leave request submitted.');
    }

    public function leaveApprove(Request $request, string $id): RedirectResponse
    {
        try {
            $this->leaveService->approveLeave($id, $this->demoActorId(), $request->input('review_notes'));
            return redirect()->route('portals.staff.hr.leave')
                ->with('success', 'Leave approved.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.leave')
                ->with('error', $e->getMessage());
        }
    }

    public function leaveReject(Request $request, string $id): RedirectResponse
    {
        try {
            $this->leaveService->rejectLeave($id, $this->demoActorId(), $request->input('review_notes'));
            return redirect()->route('portals.staff.hr.leave')
                ->with('success', 'Leave rejected.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.leave')
                ->with('error', $e->getMessage());
        }
    }

    public function leaveWithdraw(string $id): RedirectResponse
    {
        try {
            $this->leaveService->withdrawLeave($id);
            return redirect()->route('portals.staff.hr.leave')
                ->with('success', 'Leave request withdrawn.');
        } catch (\Throwable $e) {
            return redirect()->route('portals.staff.hr.leave')
                ->with('error', $e->getMessage());
        }
    }
}
