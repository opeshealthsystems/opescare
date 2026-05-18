@extends('layouts.portal')

@section('title', __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support')

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
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link">
        <i data-lucide="syringe"></i>
        <span>{{ __('public.portal.nav_immunizations', [], app()->getLocale()) ?: 'Immunizations' }}</span>
    </a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link">
        <i data-lucide="send"></i>
        <span>{{ __('public.portal.nav_referrals', [], app()->getLocale()) ?: 'Referrals' }}</span>
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
@section('breadcrumb_section', __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">{{ __('public.portal.nav_support', [], app()->getLocale()) ?: 'Support' }}</h1>
        <p class="page-subtitle">{{ __('public.staff_portal.support_subtitle', [], app()->getLocale()) ?: 'Manage support tickets and escalations.' }}</p>
    </div>
    <div class="page-actions">
        <a href="{{ route('portals.staff.support') }}?create=1" class="btn btn-primary">
            <i data-lucide="plus" style="width:14px;height:14px;"></i>
            {{ __('public.staff_portal.create_ticket', [], app()->getLocale()) ?: 'Create Ticket' }}
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<form method="GET" action="{{ route('portals.staff.support') }}" class="filter-bar">
    <select name="status" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_statuses', [], app()->getLocale()) ?: 'All Statuses' }}</option>
        <option value="open"        @selected(request('status') === 'open')>{{ __('public.staff_portal.status_open', [], app()->getLocale()) ?: 'Open' }}</option>
        <option value="in_progress" @selected(request('status') === 'in_progress')>{{ __('public.staff_portal.status_in_progress', [], app()->getLocale()) ?: 'In Progress' }}</option>
        <option value="escalated"   @selected(request('status') === 'escalated')>{{ __('public.staff_portal.status_escalated', [], app()->getLocale()) ?: 'Escalated' }}</option>
        <option value="resolved"    @selected(request('status') === 'resolved')>{{ __('public.staff_portal.status_resolved', [], app()->getLocale()) ?: 'Resolved' }}</option>
        <option value="closed"      @selected(request('status') === 'closed')>{{ __('public.staff_portal.status_closed', [], app()->getLocale()) ?: 'Closed' }}</option>
    </select>
    <select name="priority" class="form-control">
        <option value="">{{ __('public.staff_portal.filter_all_priorities', [], app()->getLocale()) ?: 'All Priorities' }}</option>
        <option value="critical" @selected(request('priority') === 'critical')>{{ __('public.staff_portal.priority_critical', [], app()->getLocale()) ?: 'Critical' }}</option>
        <option value="urgent"   @selected(request('priority') === 'urgent')>{{ __('public.staff_portal.priority_urgent', [], app()->getLocale()) ?: 'Urgent' }}</option>
        <option value="high"     @selected(request('priority') === 'high')>{{ __('public.staff_portal.priority_high', [], app()->getLocale()) ?: 'High' }}</option>
        <option value="normal"   @selected(request('priority') === 'normal')>{{ __('public.staff_portal.priority_normal', [], app()->getLocale()) ?: 'Normal' }}</option>
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
    <div class="panel-body" style="padding: 0;">
        @if(count($tickets) === 0)
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i data-lucide="inbox"></i>
                </div>
                <h3>{{ __('public.staff_portal.no_tickets_title', [], app()->getLocale()) ?: 'No Tickets Found' }}</h3>
                <p>{{ __('public.staff_portal.no_tickets_desc', [], app()->getLocale()) ?: 'There are no support tickets matching your current filters.' }}</p>
                <div style="margin-top: var(--p-space-4);">
                    <a href="{{ route('portals.staff.support') }}?create=1" class="btn btn-primary">
                        <i data-lucide="plus" style="width:14px;height:14px;"></i>
                        {{ __('public.staff_portal.create_ticket', [], app()->getLocale()) ?: 'Create Ticket' }}
                    </a>
                </div>
            </div>
        @else
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>{{ __('public.staff_portal.col_ticket_no', [], app()->getLocale()) ?: 'Ticket #' }}</th>
                            <th>{{ __('public.staff_portal.col_subject', [], app()->getLocale()) ?: 'Subject' }}</th>
                            <th>{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}</th>
                            <th>{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}</th>
                            <th>{{ __('public.staff_portal.col_created', [], app()->getLocale()) ?: 'Created' }}</th>
                            <th>{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tickets as $ticket)
                        @php
                            $priorityBadge = match($ticket->priority ?? '') {
                                'critical' => 'badge-critical',
                                'urgent'   => 'badge-danger',
                                'high'     => 'badge-warning',
                                default    => 'badge-neutral',
                            };
                            $statusBadge = match($ticket->status ?? '') {
                                'resolved'    => 'badge-success',
                                'closed'      => 'badge-neutral',
                                'escalated'   => 'badge-danger',
                                'in_progress' => 'badge-teal',
                                'open'        => 'badge-primary',
                                default       => 'badge-neutral',
                            };
                            $priorityLabel = ucfirst($ticket->priority ?? 'normal');
                            $statusLabel   = ucwords(str_replace('_', ' ', $ticket->status ?? 'open'));
                        @endphp
                        <tr>
                            <td data-label="{{ __('public.staff_portal.col_ticket_no', [], app()->getLocale()) ?: 'Ticket #' }}">
                                <strong style="font-family: monospace; font-size: var(--p-text-xs);">
                                    {{ $ticket->ticket_number ?? $ticket->reference ?? '#' . ($ticket->id ?? '?') }}
                                </strong>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_subject', [], app()->getLocale()) ?: 'Subject' }}">
                                {{ $ticket->subject ?? '--' }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_priority', [], app()->getLocale()) ?: 'Priority' }}">
                                <span class="badge {{ $priorityBadge }}">{{ $priorityLabel }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_status', [], app()->getLocale()) ?: 'Status' }}">
                                <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_created', [], app()->getLocale()) ?: 'Created' }}">
                                {{ \Carbon\Carbon::parse($ticket->created_at)->format('M d, Y') }}
                            </td>
                            <td data-label="{{ __('public.staff_portal.col_actions', [], app()->getLocale()) ?: 'Actions' }}">
                                <a href="{{ route('portals.staff.support') }}?view={{ $ticket->id ?? $ticket->uuid ?? '' }}" class="btn btn-ghost btn-sm">
                                    <i data-lucide="eye" style="width:13px;height:13px;"></i>
                                    {{ __('public.staff_portal.action_view', [], app()->getLocale()) ?: 'View' }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@endsection
