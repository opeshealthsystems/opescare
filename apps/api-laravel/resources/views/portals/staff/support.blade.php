@extends('layouts.portal')

@section('title', 'Support Desk — OpesCare Staff Portal')

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Support Desk')

@section('sidebar_role_badge')
    <div class="sidebar-role-badge">
        <i data-lucide="stethoscope" style="width:0.75rem;height:0.75rem;display:inline;vertical-align:middle;margin-right:4px;"></i>
        {{ __('public.staff_portal.role_label', [], app()->getLocale()) ?: 'Clinical Staff' }}
    </div>
@endsection

@section('sidebar_nav')
    <div class="sidebar-section-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i> Dashboard</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Clinical</div>
    <a href="{{ route('portals.staff.appointments') }}" class="sidebar-link"><i data-lucide="calendar-check-2"></i> Appointments</a>
    <a href="{{ route('portals.staff.queue') }}" class="sidebar-link"><i data-lucide="list-ordered"></i> Patient Queue</a>
    <a href="{{ route('portals.staff.immunizations') }}" class="sidebar-link"><i data-lucide="syringe"></i> Immunizations</a>
    <a href="{{ route('portals.staff.referrals') }}" class="sidebar-link"><i data-lucide="send"></i> Referrals</a>

    <div class="sidebar-section-label" style="margin-top:var(--p-space-4);">Operations</div>
    <a href="{{ route('portals.staff.billing') }}" class="sidebar-link"><i data-lucide="receipt"></i> Billing</a>
    <a href="{{ route('portals.staff.support') }}" class="sidebar-link active"><i data-lucide="headset"></i> Support</a>
@endsection

@section('sidebar_user_role', 'Clinical Staff')

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Support Desk</h1>
        <p class="page-subtitle">View and manage support tickets, incidents, and helpdesk requests.</p>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">
            <i data-lucide="headset"></i>
            Support Tickets
        </h2>
        @if(!empty($tickets) && count($tickets))
        <span class="badge badge-primary">{{ count($tickets) }} tickets</span>
        @endif
    </div>

    @if(empty($tickets) || count($tickets) === 0)
        <div class="empty-state">
            <div class="empty-state-icon">
                <i data-lucide="check-circle-2"></i>
            </div>
            <h3>No Open Tickets</h3>
            <p>All support tickets are resolved. Well done!</p>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table" aria-label="Support tickets">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Requester</th>
                        <th>Priority</th>
                        <th>SLA Due</th>
                        <th>Status</th>
                        <th class="td-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                    <tr>
                        <td data-label="Subject">
                            <div class="td-strong">{{ $ticket->subject ?? '—' }}</div>
                            <div class="td-muted">{{ $ticket->category ?? '' }}</div>
                        </td>
                        <td data-label="Requester">
                            <span class="td-muted">{{ $ticket->requester_id ?? '—' }}</span>
                        </td>
                        <td data-label="Priority">
                            @php
                                $prCls = match($ticket->priority ?? 'normal') {
                                    'critical'  => 'badge-critical',
                                    'urgent'    => 'badge-danger',
                                    'high'      => 'badge-warning',
                                    default     => 'badge-neutral',
                                };
                            @endphp
                            <span class="badge {{ $prCls }}">{{ ucfirst($ticket->priority ?? 'normal') }}</span>
                        </td>
                        <td data-label="SLA Due">
                            <span class="td-muted">
                                {{ $ticket->sla_due_at ? \Carbon\Carbon::parse($ticket->sla_due_at)->format('d M H:i') : '—' }}
                            </span>
                        </td>
                        <td data-label="Status">
                            @php
                                $stCls = match($ticket->status ?? 'open') {
                                    'resolved'   => 'badge-success',
                                    'closed'     => 'badge-neutral',
                                    'escalated'  => 'badge-danger',
                                    'in_progress'=> 'badge-teal',
                                    default      => 'badge-primary',
                                };
                            @endphp
                            <span class="badge {{ $stCls }}">{{ ucfirst(str_replace('_', ' ', $ticket->status ?? 'open')) }}</span>
                        </td>
                        <td data-label="Actions" class="td-actions">
                            <button class="btn btn-sm btn-secondary" title="View ticket">
                                <i data-lucide="eye" style="width:0.85rem;height:0.85rem;"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection
