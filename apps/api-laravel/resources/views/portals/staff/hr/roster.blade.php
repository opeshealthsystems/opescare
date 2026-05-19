@extends('layouts.portal')

@section('title', 'Duty Roster')

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
    <a href="{{ route('portals.staff.analytics') }}" class="sidebar-link">
        <i data-lucide="bar-chart-2"></i>
        <span>{{ __('public.portal.nav_analytics', [], app()->getLocale()) ?: 'Analytics' }}</span>
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
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
        <i data-lucide="clock"></i>
        <span>{{ __('public.portal.nav_staff_shifts', [], app()->getLocale()) ?: 'Shifts' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link active">
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
    <div class="sidebar-nav-label">Supply Chain</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i>
        <span>Supply Chain</span>
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
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
    <a href="{{ route('portals.staff.search') }}" class="sidebar-link {{ request()->routeIs('portals.staff.search') ? 'active' : '' }}">
        <i data-lucide="search"></i>
        <span>{{ __('public.portal.nav_search', [], app()->getLocale()) ?: 'Global Search' }}</span>
    </a>
    <a href="{{ route('portals.staff.files.index') }}" class="sidebar-link {{ request()->routeIs('portals.staff.files*') ? 'active' : '' }}">
        <i data-lucide="paperclip"></i>
        <span>{{ __('public.portal.nav_files', [], app()->getLocale()) ?: 'Files & Attachments' }}</span>
    </a>
    <a href="{{ route('portals.staff.wards') }}" class="sidebar-link {{ request()->routeIs('portals.staff.wards*') ? 'active' : '' }}">
        <i data-lucide="bed"></i>
        <span>{{ __('public.portal.nav_wards', [], app()->getLocale()) ?: 'Wards & Beds' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Duty Roster')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Duty Roster</h1>
        <p class="page-subtitle">Create and publish staff duty schedules by department and period.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openRosterModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        New Roster
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

{{-- Filters --}}
<form method="GET" action="{{ route('portals.staff.hr.roster') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['draft','published','archived'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    @if($departments->isNotEmpty())
    <select name="department" class="form-control">
        <option value="">All Departments</option>
        @foreach($departments as $d)
            <option value="{{ $d }}" {{ request('department') === $d ? 'selected' : '' }}>{{ $d }}</option>
        @endforeach
    </select>
    @endif
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.hr.roster') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($rosters->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="calendar-range"></i></div>
                <h3>No Rosters Yet</h3>
                <p>Create a duty roster to schedule staff shifts for a period.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openRosterModal()">New Roster</button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Department</th>
                            <th>Period</th>
                            <th>Assignments</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rosters as $roster)
                        @php
                            $rBadge = match($roster->status) {
                                'draft'     => 'badge-warning',
                                'published' => 'badge-success',
                                'archived'  => 'badge-neutral',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Department"><strong>{{ $roster->department }}</strong></td>
                            <td data-label="Period">
                                {{ \Carbon\Carbon::parse($roster->period_start)->format('M d') }} –
                                {{ \Carbon\Carbon::parse($roster->period_end)->format('M d, Y') }}
                            </td>
                            <td data-label="Assignments">{{ $roster->assignments_count }}</td>
                            <td data-label="Status">
                                <span class="badge {{ $rBadge }}">{{ ucfirst($roster->status) }}</span>
                            </td>
                            <td data-label="Published">
                                {{ $roster->published_at ? \Carbon\Carbon::parse($roster->published_at)->format('M d, Y') : '—' }}
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($roster->status === 'draft')
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openAssignModal('{{ $roster->id }}', '{{ addslashes($roster->department) }}')">
                                            <i data-lucide="user-plus" style="width:11px;height:11px;"></i>
                                            Assign
                                        </button>
                                        <form method="POST" action="{{ route('portals.staff.hr.roster.publish', $roster->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-xs">
                                                <i data-lucide="send" style="width:11px;height:11px;"></i>
                                                Publish
                                            </button>
                                        </form>
                                    @endif
                                    @if(in_array($roster->status, ['draft','published']))
                                        <form method="POST" action="{{ route('portals.staff.hr.roster.archive', $roster->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Archive this roster?')">
                                                <i data-lucide="archive" style="width:11px;height:11px;"></i>
                                                Archive
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- New Roster Modal --}}
<div id="roster-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:440px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">New Duty Roster</h3>
        <form method="POST" action="{{ route('portals.staff.hr.roster.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Department *</label>
                <input type="text" name="department" class="form-control" required maxlength="100" placeholder="e.g. Emergency, ICU, Lab">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Period Start *</label>
                    <input type="date" name="period_start" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Period End *</label>
                    <input type="date" name="period_end" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeRosterModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="calendar-range" style="width:13px;height:13px;"></i>
                    Create Roster
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Assign Staff Modal --}}
<div id="assign-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:460px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Assign Staff to Roster</h3>
        <p id="assign-dept-label" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="assign-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Staff Member *</label>
                <select name="staff_profile_id" class="form-control" required>
                    <option value="">— Select —</option>
                    @foreach($staff as $member)
                        <option value="{{ $member->id }}">{{ $member->full_name }} ({{ $member->job_title ?? $member->staff_category }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Shift *</label>
                <select name="staff_shift_id" class="form-control" required>
                    <option value="">— Select —</option>
                    @foreach($shifts as $shift)
                        <option value="{{ $shift->id }}">{{ $shift->name }} ({{ $shift->start_time }}–{{ $shift->end_time }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Work Date *</label>
                <input type="date" name="work_date" class="form-control" required>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="2" maxlength="300"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAssignModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="user-plus" style="width:13px;height:13px;"></i>
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openRosterModal()  { document.getElementById('roster-modal').style.display = 'flex'; }
    function closeRosterModal() { document.getElementById('roster-modal').style.display = 'none'; }
    document.getElementById('roster-modal').addEventListener('click', function(e) {
        if (e.target === this) closeRosterModal();
    });

    function openAssignModal(rosterId, dept) {
        document.getElementById('assign-dept-label').textContent = 'Department: ' + dept;
        document.getElementById('assign-form').action = '{{ url('/portals/staff/hr/roster') }}/' + rosterId + '/assign';
        document.getElementById('assign-modal').style.display = 'flex';
    }
    function closeAssignModal() { document.getElementById('assign-modal').style.display = 'none'; }
    document.getElementById('assign-modal').addEventListener('click', function(e) {
        if (e.target === this) closeAssignModal();
    });
</script>
@endsection
