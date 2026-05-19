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
                            $canClose = in_array($ticket->status ?? '', ['open','assigned','in_progress','escalated']);
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_subject', [], app()->getLocale()) ?: 'Subject' }}">
                                {{ $ticket->subject ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_type', [], app()->getLocale()) ?: 'Category' }}">
                                <span class="badge badge-neutral">{{ ucwords($ticket->category ?? '') }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}">
                                <span class="badge {{ $priorityBadge }}">{{ ucwords($ticket->priority ?? '') }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ ucwords(str_replace('_', ' ', $ticket->status ?? '')) }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_created', [], app()->getLocale()) ?: 'Created' }}">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
                                    @if($canClose)
                                        <button type="button" class="btn btn-ghost btn-xs"
                                            onclick="openReplyModal('{{ $ticket->id }}')">
                                            <i data-lucide="message-circle" style="width:11px;height:11px;"></i>
                                            {{ __('public.staff_portal.btn_reply', [], app()->getLocale()) ?: 'Reply' }}
                                        </button>
                                        <form method="POST" action="{{ route('portals.staff.support.close', $ticket->id) }}" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-xs"
                                                onclick="return confirm('Close this ticket?')">
                                                <i data-lucide="check-circle" style="width:11px;height:11px;"></i>
                                                {{ __('public.staff_portal.btn_close_ticket', [], app()->getLocale()) ?: 'Close' }}
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
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">
            {{ __('public.staff_portal.btn_new_ticket', [], app()->getLocale()) ?: 'New Support Ticket' }}
        </h3>
        <form method="POST" action="{{ route('portals.staff.support.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_subject', [], app()->getLocale()) ?: 'Subject' }} *</label>
                <input type="text" name="subject" class="form-control" required maxlength="200">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.lbl_category', [], app()->getLocale()) ?: 'Category' }} *</label>
                    <select name="category" class="form-control" required>
                        <option value="clinical">{{ __('public.staff_portal.cat_clinical', [], app()->getLocale()) ?: 'Clinical' }}</option>
                        <option value="technical">{{ __('public.staff_portal.cat_technical', [], app()->getLocale()) ?: 'Technical' }}</option>
                        <option value="billing">{{ __('public.staff_portal.cat_billing', [], app()->getLocale()) ?: 'Billing' }}</option>
                        <option value="access">{{ __('public.staff_portal.cat_access', [], app()->getLocale()) ?: 'Access / Permissions' }}</option>
                        <option value="other">{{ __('public.staff_portal.cat_other', [], app()->getLocale()) ?: 'Other' }}</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">{{ __('public.staff_portal.lbl_priority', [], app()->getLocale()) ?: 'Priority' }} *</label>
                    <select name="priority" class="form-control" required>
                        <option value="normal">{{ __('public.staff_portal.priority_normal', [], app()->getLocale()) ?: 'Normal' }}</option>
                        <option value="high">{{ __('public.staff_portal.priority_high', [], app()->getLocale()) ?: 'High' }}</option>
                        <option value="urgent">{{ __('public.staff_portal.priority_urgent', [], app()->getLocale()) ?: 'Urgent' }}</option>
                        <option value="critical">{{ __('public.staff_portal.priority_critical', [], app()->getLocale()) ?: 'Critical' }}</option>
                    </select>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_description', [], app()->getLocale()) ?: 'Description' }} *</label>
                <textarea name="description" class="form-control" rows="4" required minlength="10" maxlength="2000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeTicketModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send" style="width:13px;height:13px;"></i>
                    {{ __('public.staff_portal.btn_new_ticket', [], app()->getLocale()) ?: 'Submit Ticket' }}
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Reply Modal --}}
<div id="reply-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;">
        <h3 style="margin:0 0 1.25rem;font-size:1.1rem;">
            {{ __('public.staff_portal.btn_reply', [], app()->getLocale()) ?: 'Reply to Ticket' }}
        </h3>
        <form id="reply-form" method="POST" action="">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">{{ __('public.staff_portal.lbl_reply', [], app()->getLocale()) ?: 'Your Reply' }} *</label>
                <textarea name="body" class="form-control" rows="4" required minlength="2" maxlength="2000"></textarea>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:1rem;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeReplyModal()">Back</button>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i data-lucide="send" style="width:13px;height:13px;"></i>
                    {{ __('public.staff_portal.btn_reply', [], app()->getLocale()) ?: 'Send Reply' }}
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openTicketModal() { document.getElementById('ticket-modal').style.display = 'flex'; }
    function closeTicketModal() { document.getElementById('ticket-modal').style.display = 'none'; }
    document.getElementById('ticket-modal').addEventListener('click', function(e) {
        if (e.target === this) closeTicketModal();
    });

    function openReplyModal(ticketId) {
        var form = document.getElementById('reply-form');
        var base = '{{ url('/portals/staff/support') }}';
        form.setAttribute('action', base + '/' + ticketId + '/reply');
        document.getElementById('reply-modal').style.display = 'flex';
    }
    function closeReplyModal() { document.getElementById('reply-modal').style.display = 'none'; }
    document.getElementById('reply-modal').addEventListener('click', function(e) {
        if (e.target === this) closeReplyModal();
    });
</script>
@endsection
