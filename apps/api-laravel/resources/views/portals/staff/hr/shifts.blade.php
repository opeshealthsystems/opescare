@extends('layouts.portal')

@section('title', 'Shift Management')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">{{ __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff' }}</div>
@endsection
@section('sidebar_user_role', __('public.staff_portal.role_clinical_staff', [], app()->getLocale()) ?: 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link">
        <i data-lucide="layout-dashboard"></i>
        <span>{{ __('public.portal.nav_dashboard', [], app()->getLocale()) ?: 'Dashboard' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link">
        <i data-lucide="calendar-check-2"></i>
        <span>{{ __('public.portal.nav_appointments', [], app()->getLocale()) ?: 'Appointments' }}</span>
    </a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link">
        <i data-lucide="list-ordered"></i>
        <span>{{ __('public.portal.nav_queue', [], app()->getLocale()) ?: 'Patient Queue' }}</span>
    </a>
    <a href="{{ route('portals.staff.visits') }}" class="sidebar-link">
        <i data-lucide="stethoscope"></i>
        <span>{{ __('public.portal.nav_visits', [], app()->getLocale()) ?: 'Visits' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">HR & Staff</div>
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link active">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link">
        <i data-lucide="plane-takeoff"></i>
        <span>{{ __('public.portal.nav_staff_leave', [], app()->getLocale()) ?: 'Leave' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Inventory</div>
    <a href="{{ route('portals.staff.inventory.pharmacy') }}" class="sidebar-link">
        <i data-lucide="pill"></i>
        <span>{{ __('public.portal.nav_inventory_pharmacy', [], app()->getLocale()) ?: 'Pharmacy' }}</span>
    </a>
    <a href="{{ route('portals.staff.inventory.blood') }}" class="sidebar-link">
        <i data-lucide="droplets"></i>
        <span>{{ __('public.portal.nav_inventory_blood', [], app()->getLocale()) ?: 'Blood Bank' }}</span>
    </a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link">
        <i data-lucide="receipt"></i>
        <span>{{ __('public.portal.nav_billing', [], app()->getLocale()) ?: 'Billing' }}</span>
    </a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Shifts')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Shift Definitions</h1>
        <p class="page-subtitle">Define the shift templates used across duty rosters.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openShiftModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        New Shift
    </button>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;">
        <i data-lucide="check-circle"></i><div>{{ session('success') }}</div>
    </div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;">
        <i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div>
    </div>
@endif

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($shifts->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="clock"></i></div>
                <h3>No Shifts Defined</h3>
                <p>Create shift templates like Morning, Afternoon, Night, or On-Call.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openShiftModal()">
                    New Shift
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Duration</th>
                            <th>Crosses Midnight</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shifts as $shift)
                        <tr>
                            <td data-label="Name"><strong>{{ $shift->name }}</strong></td>
                            <td data-label="Department">{{ $shift->department ?? '—' }}</td>
                            <td data-label="Start">{{ $shift->start_time }}</td>
                            <td data-label="End">{{ $shift->end_time }}</td>
                            <td data-label="Duration">{{ $shift->duration_hours ? $shift->duration_hours . 'h' : '—' }}</td>
                            <td data-label="Crosses Midnight">
                                @if($shift->crosses_midnight)
                                    <span class="badge badge-warning">Yes</span>
                                @else
                                    <span class="badge badge-neutral">No</span>
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $shift->status === 'active' ? 'badge-success' : 'badge-neutral' }}">
                                    {{ ucfirst($shift->status) }}
                                </span>
                            </td>
                            <td data-label="Actions">
                                <form method="POST" action="{{ route('portals.staff.hr.shifts.toggle', $shift->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-xs">
                                        <i data-lucide="{{ $shift->status === 'active' ? 'pause-circle' : 'play-circle' }}" style="width:11px;height:11px;"></i>
                                        {{ $shift->status === 'active' ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- New Shift Modal --}}
<div id="shift-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">New Shift</h3>
        <form method="POST" action="{{ route('portals.staff.hr.shifts.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Shift Name *</label>
                <input type="text" name="name" class="form-control" required maxlength="100" placeholder="e.g. Morning, Night, On-Call">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Department</label>
                <input type="text" name="department" class="form-control" maxlength="100" placeholder="Leave blank for all departments">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time *</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Duration (hours)</label>
                    <input type="number" name="duration_hours" class="form-control" min="1" max="24" placeholder="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Crosses Midnight?</label>
                    <select name="crosses_midnight" class="form-control">
                        <option value="0">No</option>
                        <option value="1">Yes</option>
                    </select>
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeShiftModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="clock" style="width:13px;height:13px;"></i>
                    Create Shift
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openShiftModal()  { document.getElementById('shift-modal').style.display = 'flex'; }
    function closeShiftModal() { document.getElementById('shift-modal').style.display = 'none'; }
    document.getElementById('shift-modal').addEventListener('click', function(e) {
        if (e.target === this) closeShiftModal();
    });
</script>
@endsection
