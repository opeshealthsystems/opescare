@extends('layouts.portal')

@section('title', 'Leave Management')

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
    <a href="{{ route('portals.staff.hr.roster') }}" class="sidebar-link">
        <i data-lucide="calendar-range"></i>
        <span>{{ __('public.portal.nav_staff_roster', [], app()->getLocale()) ?: 'Duty Roster' }}</span>
    </a>
    <a href="{{ route('portals.staff.hr.leave') }}" class="sidebar-link active">
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
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Leave')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Leave Management</h1>
        <p class="page-subtitle">Submit and review staff leave requests.</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openLeaveModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        New Leave Request
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
<form method="GET" action="{{ route('portals.staff.hr.leave') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">All Statuses</option>
        @foreach(['pending','approved','rejected','withdrawn','cancelled'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="leave_type" class="form-control">
        <option value="">All Types</option>
        @foreach(['annual','sick','emergency','maternity','paternity','study','unpaid'] as $t)
            <option value="{{ $t }}" {{ request('leave_type') === $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i> Filter
    </button>
    <a href="{{ route('portals.staff.hr.leave') }}" class="btn btn-ghost btn-sm">Clear</a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($requests->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="plane-takeoff"></i></div>
                <h3>No Leave Requests</h3>
                <p>Submit leave requests on behalf of staff members here.</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openLeaveModal()">New Leave Request</button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Staff Member</th>
                            <th>Type</th>
                            <th>Period</th>
                            <th>Days</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        @php
                            $lBadge = match($req->status) {
                                'pending'   => 'badge-warning',
                                'approved'  => 'badge-success',
                                'rejected'  => 'badge-danger',
                                'withdrawn' => 'badge-neutral',
                                'cancelled' => 'badge-neutral',
                                default     => 'badge-neutral',
                            };
                        @endphp
                        <tr>
                            <td data-label="Staff Member">
                                <strong>{{ $req->staffProfile?->full_name ?? '—' }}</strong>
                            </td>
                            <td data-label="Type">
                                <span class="badge badge-neutral">{{ ucfirst($req->leave_type) }}</span>
                            </td>
                            <td data-label="Period">
                                {{ \Carbon\Carbon::parse($req->start_date)->format('M d') }} –
                                {{ \Carbon\Carbon::parse($req->end_date)->format('M d, Y') }}
                            </td>
                            <td data-label="Days">{{ $req->days_requested ?? '—' }}</td>
                            <td data-label="Status">
                                <span class="badge {{ $lBadge }}">{{ ucfirst($req->status) }}</span>
                            </td>
                            <td data-label="Reviewed By">
                                <span style="font-size:var(--p-text-xs);">{{ $req->reviewed_by ?? '—' }}</span>
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($req->status === 'pending')
                                        <button type="button" class="btn btn-success btn-xs"
                                            onclick="openReviewModal('{{ $req->id }}', 'approve')">
                                            <i data-lucide="check" style="width:11px;height:11px;"></i>
                                            Approve
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openReviewModal('{{ $req->id }}', 'reject')">
                                            <i data-lucide="x" style="width:11px;height:11px;"></i>
                                            Reject
                                        </button>
                                    @endif
                                    @if(in_array($req->status, ['pending','approved']))
                                        <form method="POST" action="{{ route('portals.staff.hr.leave.withdraw', $req->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-xs"
                                                onclick="return confirm('Withdraw this leave request?')">
                                                <i data-lucide="undo-2" style="width:11px;height:11px;"></i>
                                                Withdraw
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

{{-- New Leave Request Modal --}}
<div id="leave-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">New Leave Request</h3>
        <form method="POST" action="{{ route('portals.staff.hr.leave.store') }}">
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
                <label class="form-label">Leave Type *</label>
                <select name="leave_type" class="form-control" required>
                    @foreach(['annual','sick','emergency','maternity','paternity','study','unpaid'] as $t)
                        <option value="{{ $t }}">{{ ucfirst($t) }}</option>
                    @endforeach
                </select>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date *</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Reason</label>
                <textarea name="reason" class="form-control" rows="3" maxlength="1000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeLeaveModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send" style="width:13px;height:13px;"></i>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Review Modal (approve / reject) --}}
<div id="review-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:400px;margin:1rem;">
        <h3 id="review-modal-title" style="margin:0 0 1.25rem;font-size:1.1rem;">Review Leave</h3>
        <form id="review-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Review Notes</label>
                <textarea name="review_notes" class="form-control" rows="3" maxlength="500"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeReviewModal()">Cancel</button>
                <button type="submit" id="review-submit-btn" class="btn btn-primary btn-sm">Submit</button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openLeaveModal()  { document.getElementById('leave-modal').style.display = 'flex'; }
    function closeLeaveModal() { document.getElementById('leave-modal').style.display = 'none'; }
    document.getElementById('leave-modal').addEventListener('click', function(e) {
        if (e.target === this) closeLeaveModal();
    });

    function openReviewModal(requestId, action) {
        var base = '{{ url('/portals/staff/hr/leave') }}';
        document.getElementById('review-form').action = base + '/' + requestId + '/' + action;
        document.getElementById('review-modal-title').textContent = action === 'approve' ? 'Approve Leave' : 'Reject Leave';
        var btn = document.getElementById('review-submit-btn');
        btn.className = action === 'approve' ? 'btn btn-success btn-sm' : 'btn btn-danger btn-sm';
        btn.textContent = action === 'approve' ? 'Approve' : 'Reject';
        document.getElementById('review-modal').style.display = 'flex';
    }
    function closeReviewModal() { document.getElementById('review-modal').style.display = 'none'; }
    document.getElementById('review-modal').addEventListener('click', function(e) {
        if (e.target === this) closeReviewModal();
    });
</script>
@endsection
