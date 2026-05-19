@extends('layouts.portal')

@section('title', __('public.staff_portal.support_title', [], app()->getLocale()) ?: 'Support')

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
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link active">
        <i data-lucide="headset"></i>
        <span>{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</span>
    </a>
    <a href="{{ route('portals.insurance.policies') }}" class="sidebar-link">
        <i data-lucide="shield-check"></i>
        <span>{{ __('public.portal.nav_insurance', [], app()->getLocale()) ?: 'Insurance' }}</span>
    </a>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link">
        <i data-lucide="upload-cloud"></i>
        <span>{{ __('public.portal.nav_data_import', [], app()->getLocale()) ?: 'Data Import' }}</span>
    </a>
</div>
@endsection

@section('breadcrumb_home', __('public.staff_portal.title', [], app()->getLocale()) ?: 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', __('public.staff_portal.support_title', [], app()->getLocale()) ?: 'Support')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.staff_portal.support_title', [], app()->getLocale()) ?: 'Support & Help Desk' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.support_subtitle', [], app()->getLocale()) ?: 'Submit and track support tickets.' }}</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm" onclick="openTicketModal()">
        <i data-lucide="plus-circle" style="width:14px;height:14px;"></i>
        {{ __('public.staff_portal.btn_new_ticket', [], app()->getLocale()) ?: 'New Ticket' }}
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
<form method="GET" action="{{ route('portals.staff.support') }}" class="filter-bar" style="flex-wrap:wrap;gap:.5rem;">
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        @foreach(['open','assigned','in_progress','escalated','resolved','closed'] as $s)
            <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
        @endforeach
    </select>
    <select name="priority" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_priorities', [], app()->getLocale()) ?: 'All Priorities' }}</option>
        @foreach(['normal','high','urgent','critical'] as $p)
            <option value="{{ $p }}" {{ request('priority') === $p ? 'selected' : '' }}>{{ ucwords($p) }}</option>
        @endforeach
    </select>
    <select name="category" class="form-control">
        <option value="">All Categories</option>
        @foreach(['clinical','technical','billing','access','other'] as $c)
            <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ ucwords($c) }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-primary btn-sm">
        <i data-lucide="filter" style="width:13px;height:13px;"></i>
        {{ __('public.staff_portal.filter_apply', [], app()->getLocale()) ?: 'Filter' }}
    </button>
    <a href="{{ route('portals.staff.support') }}" class="btn btn-ghost btn-sm">
        {{ __('public.staff_portal.filter_clear', [], app()->getLocale()) ?: 'Clear' }}
    </a>
</form>

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if(count($tickets) === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="ticket"></i></div>
                <h3>{{ __('public.staff_portal.no_tickets_title', [], app()->getLocale()) ?: 'No Support Tickets' }}</h3>
                <p>{{ __('public.staff_portal.no_tickets_desc', [], app()->getLocale()) ?: 'There are no support tickets matching your current filters.' }}</p>
                <button type="button" class="btn btn-primary btn-sm" style="margin-top:1rem;" onclick="openTicketModal()">
                    {{ __('public.staff_portal.btn_new_ticket', [], app()->getLocale()) ?: 'New Ticket' }}
                </button>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_subject', [], app()->getLocale()) ?: 'Subject' }}</th>
                            <th>{{ __('public.staff_portal.col_type', [], app()->getLocale()) ?: 'Category' }}</th>
                            <th>{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>SLA Due</th>
                            <th>Messages</th>
                            <th>{{ __('public.staff_portal.col_created', [], app()->getLocale()) ?: 'Created' }}</th>
                            <th>{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        @php
                            $statusBadge = match($ticket->status ?? '') {
                                'open'        => 'badge-primary',
                                'assigned'    => 'badge-teal',
                                'in_progress' => 'badge-warning',
                                'escalated'   => 'badge-danger',
                                'resolved'    => 'badge-success',
                                'closed'      => 'badge-neutral',
                                default       => 'badge-neutral',
                            };
                            $priorityBadge = match($ticket->priority ?? '') {
                                'critical' => 'badge-danger',
                                'urgent'   => 'badge-warning',
                                'high'     => 'badge-primary',
                                default    => 'badge-neutral',
                            };
                            $canAct   = in_array($ticket->status ?? '', ['open','assigned','in_progress','escalated']);
                            $slaDate  = $ticket->sla_due_at ? \Carbon\Carbon::parse($ticket->sla_due_at) : null;
                            $slaOver  = $slaDate && $slaDate->isPast() && !in_array($ticket->status, ['resolved','closed']);
                            $slaSoon  = $slaDate && !$slaOver && $slaDate->diffInHours(now()) < 4 && !in_array($ticket->status, ['resolved','closed']);
                        @endphp
                        <tr>
                            <td data-label="Subject">
                                <span style="font-weight:500;">{{ $ticket->subject ?? '--' }}</span>
                                @if($ticket->assigned_to)
                                    <div style="font-size:.75rem;color:var(--p-text-muted);">Assigned: {{ $ticket->assigned_to }}</div>
                                @endif
                            </td>
                            <td data-label="Category">
                                <span class="badge badge-neutral">{{ ucwords($ticket->category ?? '') }}</span>
                            </td>
                            <td data-label="Priority">
                                <span class="badge {{ $priorityBadge }}">{{ ucwords($ticket->priority ?? '') }}</span>
                            </td>
                            <td data-label="Status">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $ticket->status ?? '')) }}</span>
                                @if($ticket->escalation_level)
                                    <div style="font-size:.72rem;color:var(--p-danger);">↑ {{ strtoupper($ticket->escalation_level) }}</div>
                                @endif
                            </td>
                            <td data-label="SLA Due">
                                @if($slaDate)
                                    <span style="font-size:.8rem;{{ $slaOver ? 'color:var(--p-danger);font-weight:600;' : ($slaSoon ? 'color:var(--p-warning);' : 'color:var(--p-text-muted);') }}">
                                        @if($slaOver)
                                            <i data-lucide="alert-triangle" style="width:11px;height:11px;"></i>
                                            Overdue
                                        @else
                                            {{ $slaDate->format('M d, H:i') }}
                                        @endif
                                    </span>
                                @else
                                    <span style="color:var(--p-text-muted);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td data-label="Messages">
                                @if(($ticket->messages_count ?? 0) > 0)
                                    <span class="badge badge-teal">
                                        <i data-lucide="message-circle" style="width:10px;height:10px;"></i>
                                        {{ $ticket->messages_count }}
                                    </span>
                                @else
                                    <span style="color:var(--p-text-muted);font-size:.8rem;">—</span>
                                @endif
                            </td>
                            <td data-label="Created">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}
                            </td>
                            <td data-label="Actions">
                                <div style="display:flex;gap:.3rem;flex-wrap:wrap;">
                                    @if($canAct)
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openReplyModal('{{ $ticket->id }}')">
                                            <i data-lucide="message-circle" style="width:11px;height:11px;"></i>
                                            Reply
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openAssignModal('{{ $ticket->id }}', '{{ addslashes($ticket->assigned_to ?? '') }}')">
                                            <i data-lucide="user-check" style="width:11px;height:11px;"></i>
                                            Assign
                                        </button>
                                        @if($ticket->status !== 'escalated')
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openEscalateModal('{{ $ticket->id }}')">
                                            <i data-lucide="arrow-up-circle" style="width:11px;height:11px;"></i>
                                            Escalate
                                        </button>
                                        @endif
                                        <form method="POST" action="{{ route('portals.staff.support.close', $ticket->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs"
                                                onclick="return confirm('Close this ticket?')">
                                                <i data-lucide="check-circle" style="width:11px;height:11px;"></i>
                                                Close
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

{{-- New Ticket Modal --}}
<div id="ticket-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:520px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">New Support Ticket</h3>
        <form method="POST" action="{{ route('portals.staff.support.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Subject *</label>
                <input type="text" name="subject" class="form-control" required maxlength="200">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-control" required>
                        <option value="clinical">Clinical</option>
                        <option value="technical">Technical</option>
                        <option value="billing">Billing</option>
                        <option value="access">Access / Permissions</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority *</label>
                    <select name="priority" class="form-control" required>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Description *</label>
                <textarea name="description" class="form-control" rows="4" required minlength="10" maxlength="2000"></textarea>
                <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;">
                    <i data-lucide="shield" style="width:11px;height:11px;"></i>
                    PII (Health IDs, emails, phone numbers) will be automatically redacted.
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTicketModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send" style="width:13px;height:13px;"></i>
                    Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reply Modal --}}
<div id="reply-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Reply to Ticket</h3>
        <form id="reply-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Your Reply *</label>
                <textarea name="body" class="form-control" rows="4" required minlength="2" maxlength="2000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeReplyModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send" style="width:13px;height:13px;"></i>
                    Send Reply
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Assign Modal --}}
<div id="assign-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">Assign Ticket</h3>
        <form id="assign-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Assign To *</label>
                <input type="text" id="assign-to-input" name="assigned_to" class="form-control" required maxlength="100"
                    placeholder="e.g. it-support, helpdesk@facility.org, John Doe">
                <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:.25rem;">
                    Enter staff name, email, or team identifier.
                </div>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeAssignModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="user-check" style="width:13px;height:13px;"></i>
                    Assign
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Escalate Modal --}}
<div id="escalate-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:420px;margin:1rem;">
        <h3 style="margin:0 0 .25rem;font-size:1.1rem;">Escalate Ticket</h3>
        <p style="color:var(--p-text-muted);font-size:.85rem;margin:0 0 1.25rem;">
            This will flag the ticket for higher-level attention and update the SLA.
        </p>
        <form id="escalate-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Escalation Level *</label>
                <select name="escalation_level" class="form-control" required>
                    <option value="l1">L1 — First-line support</option>
                    <option value="l2">L2 — Technical / Specialist</option>
                    <option value="l3">L3 — Engineering / Senior</option>
                    <option value="management">Management</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Reason <span style="color:var(--p-text-muted)">(optional)</span></label>
                <textarea name="reason" class="form-control" rows="3" maxlength="500"
                    placeholder="Why is this ticket being escalated?"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeEscalateModal()">Back</button>
                <button type="submit" class="btn btn-danger btn-sm">
                    <i data-lucide="arrow-up-circle" style="width:13px;height:13px;"></i>
                    Escalate
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // ── Ticket modal ──────────────────────────────────────
    function openTicketModal() { document.getElementById('ticket-modal').style.display = 'flex'; }
    function closeTicketModal() { document.getElementById('ticket-modal').style.display = 'none'; }
    document.getElementById('ticket-modal').addEventListener('click', function(e) {
        if (e.target === this) closeTicketModal();
    });

    // ── Reply modal ───────────────────────────────────────
    function openReplyModal(ticketId) {
        var form = document.getElementById('reply-form');
        form.setAttribute('action', '{{ url('/portals/staff/support') }}/' + ticketId + '/reply');
        document.getElementById('reply-modal').style.display = 'flex';
    }
    function closeReplyModal() { document.getElementById('reply-modal').style.display = 'none'; }
    document.getElementById('reply-modal').addEventListener('click', function(e) {
        if (e.target === this) closeReplyModal();
    });

    // ── Assign modal ──────────────────────────────────────
    function openAssignModal(ticketId, currentAssignee) {
        var form = document.getElementById('assign-form');
        form.setAttribute('action', '{{ url('/portals/staff/support') }}/' + ticketId + '/assign');
        document.getElementById('assign-to-input').value = currentAssignee || '';
        document.getElementById('assign-modal').style.display = 'flex';
    }
    function closeAssignModal() { document.getElementById('assign-modal').style.display = 'none'; }
    document.getElementById('assign-modal').addEventListener('click', function(e) {
        if (e.target === this) closeAssignModal();
    });

    // ── Escalate modal ────────────────────────────────────
    function openEscalateModal(ticketId) {
        var form = document.getElementById('escalate-form');
        form.setAttribute('action', '{{ url('/portals/staff/support') }}/' + ticketId + '/escalate');
        document.getElementById('escalate-modal').style.display = 'flex';
    }
    function closeEscalateModal() { document.getElementById('escalate-modal').style.display = 'none'; }
    document.getElementById('escalate-modal').addEventListener('click', function(e) {
        if (e.target === this) closeEscalateModal();
    });
</script>
@endsection
