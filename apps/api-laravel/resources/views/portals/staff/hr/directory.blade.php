@extends('layouts.portal')

@section('title', 'Staff Directory')

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
    <a href="{{ route('portals.staff.hr.directory') }}" class="sidebar-link active">
        <i data-lucide="users"></i>
        <span>{{ __('public.portal.nav_staff_directory', [], app()->getLocale()) ?: 'Directory' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.shifts') }}" class="sidebar-link">
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
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Staff Directory')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Staff Directory</h1>
        <p class="page-subtitle">Manage staff profiles, licenses, and employment records.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openAddStaffModal()">
        <i data-lucide="user-plus" style="width:14px;height:14px;"></i>
        Add Staff Member
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
<form method="GET" action="{{ route('portals.staff.hr.directory') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['active','inactive','on_leave','suspended','terminated'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="staff_category" class="form-control">
        <option value="">All Categories</option>
        @foreach(['clinical','administrative','support','management'] as $c)
            <option value="{{ $c }}" {{ request('staff_category') === $c ? 'selected' : '' }}>{{ ucfirst($c) }}</option>
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
    <input type="text" name="search" class="form-control" placeholder="Search name / ID…" value="{{ request('search') }}">
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.hr.directory') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($staff->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="users"></i></div>
                <h3>No Staff Members Found</h3>
                <p>Add your first staff member to get started.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openAddStaffModal()">
                    Add Staff Member
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Employee #</th>
                            <th>Category</th>
                            <th>Department</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Licenses</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($staff as $member)
                        @php
                            $statusBadge = match($member->status) {
                                'active'     => 'badge-success',
                                'on_leave'   => 'badge-warning',
                                'suspended'  => 'badge-danger',
                                'terminated' => 'badge-danger',
                                default      => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Name">
                                <strong>{{ $member->full_name }}</strong>
                                @if($member->job_title)
                                    <br><span style="font-size:var(--p-text-xs);color:var(--p-text-secondary);">{{ $member->job_title }}</span>
                                @endif
                            </td>
                            <td data-label="Employee #">
                                <span style="font-family:monospace;font-size:var(--p-text-xs);">{{ $member->employee_number ?? '—' }}</span>
                            </td>
                            <td data-label="Category">
                                <span class="badge badge-neutral">{{ ucfirst($member->staff_category) }}</span>
                            </td>
                            <td data-label="Department">{{ $member->department ?? '—' }}</td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ ucwords(str_replace('_',' ',$member->employment_type)) }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_',' ',$member->status)) }}</span>
                            </td>
                            <td data-label="Licenses">
                                @if($member->licenses->isNotEmpty())
                                    @foreach($member->licenses as $lic)
                                        <span class="badge {{ $lic->status === 'active' ? 'badge-success' : 'badge-danger' }}" style="font-size:.65rem;margin:.1rem;">
                                            {{ $lic->profession }}
                                        </span>
                                    @endforeach
                                @else
                                    <span style="color:var(--p-text-secondary);">—</span>
                                @endif
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openLicenseModal('{{ $member->id }}', '{{ addslashes($member->full_name) }}')">
                                        <i data-lucide="badge-check" style="width:11px;height:11px;"></i>
                                        License
                                    </button>
                                    @if($member->status !== 'terminated')
                                    <button type="button" class="btn btn-ghost btn-xs"
                                        onclick="openStatusModal('{{ $member->id }}', '{{ $member->status }}', '{{ addslashes($member->full_name) }}')">
                                        <i data-lucide="refresh-cw" style="width:11px;height:11px;"></i>
                                        Status
                                    </button>
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

{{-- Add Staff Modal --}}
<div id="add-staff-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:560px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Add Staff Member</h3>
        <form method="POST" action="{{ route('portals.staff.hr.directory.store') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control" required maxlength="100">
                </div>
                <div class="form-group">
                    <label class="form-label">Last Name *</label>
                    <input type="text" name="last_name" class="form-control" required maxlength="100">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" maxlength="200">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" maxlength="30">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Job Title</label>
                    <input type="text" name="job_title" class="form-control" maxlength="150">
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <input type="text" name="department" class="form-control" maxlength="100" placeholder="e.g. Emergency, Lab">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Staff Category *</label>
                    <select name="staff_category" class="form-control" required>
                        <option value="clinical">Clinical</option>
                        <option value="administrative">Administrative</option>
                        <option value="support">Support</option>
                        <option value="management">Management</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Employment Type *</label>
                    <select name="employment_type" class="form-control" required>
                        <option value="full_time">Full Time</option>
                        <option value="part_time">Part Time</option>
                        <option value="contract">Contract</option>
                        <option value="locum">Locum</option>
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Hire Date</label>
                    <input type="date" name="hire_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Employee Number</label>
                    <input type="text" name="employee_number" class="form-control" placeholder="Auto-generated if blank">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAddStaffModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="user-plus" style="width:13px;height:13px;"></i>
                    Add Staff
                </button>
            </div>
        </form>
    </div>
</div>

{{-- License Modal --}}
<div id="license-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:460px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Add License</h3>
        <p id="license-staff-name" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="license-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Profession *</label>
                <input type="text" name="profession" class="form-control" required placeholder="e.g. Doctor, Nurse, Pharmacist">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">License Number *</label>
                <input type="text" name="license_number" class="form-control" required maxlength="100">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Issuing Body *</label>
                <input type="text" name="issuing_body" class="form-control" required maxlength="200" placeholder="e.g. Medical Council of Cameroon">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Issue Date</label>
                    <input type="date" name="issue_date" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Expiry Date</label>
                    <input type="date" name="expiry_date" class="form-control">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeLicenseModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="badge-check" style="width:13px;height:13px;"></i>
                    Add License
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Status Modal --}}
<div id="status-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:360px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Update Staff Status</h3>
        <p id="status-staff-name" style="font-size:.85rem;color:var(--p-text-secondary);margin:0 0 1.25rem;"></p>
        <form id="status-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">New Status *</label>
                <select id="status-select" name="status" class="form-control" required></select>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openAddStaffModal()  { document.getElementById('add-staff-modal').style.display = 'flex'; }
    function closeAddStaffModal() { document.getElementById('add-staff-modal').style.display = 'none'; }
    document.getElementById('add-staff-modal').addEventListener('click', function(e) {
        if (e.target === this) closeAddStaffModal();
    });

    function openLicenseModal(staffId, staffName) {
        document.getElementById('license-staff-name').textContent = staffName;
        document.getElementById('license-form').action = '{{ url('/portals/staff/hr/directory') }}/' + staffId + '/license';
        document.getElementById('license-modal').style.display = 'flex';
    }
    function closeLicenseModal() { document.getElementById('license-modal').style.display = 'none'; }
    document.getElementById('license-modal').addEventListener('click', function(e) {
        if (e.target === this) closeLicenseModal();
    });

    var statusTransitions = {
        'active':     ['inactive','on_leave','suspended','terminated'],
        'inactive':   ['active','terminated'],
        'on_leave':   ['active','inactive','terminated'],
        'suspended':  ['active','terminated'],
        'terminated': [],
    };

    function openStatusModal(staffId, currentStatus, staffName) {
        document.getElementById('status-staff-name').textContent = staffName;
        document.getElementById('status-form').action = '{{ url('/portals/staff/hr/directory') }}/' + staffId + '/status';

        var select = document.getElementById('status-select');
        select.innerHTML = '';
        var options = statusTransitions[currentStatus] || [];
        options.forEach(function(s) {
            var opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            select.appendChild(opt);
        });

        document.getElementById('status-modal').style.display = 'flex';
    }
    function closeStatusModal() { document.getElementById('status-modal').style.display = 'none'; }
    document.getElementById('status-modal').addEventListener('click', function(e) {
        if (e.target === this) closeStatusModal();
    });
</script>
@endsection
