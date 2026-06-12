@extends('layouts.portal')
@section('title', 'Support Tickets')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Support')
@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Support Tickets</h1>
        <p class="page-subtitle">Manage and resolve platform support requests.</p>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="kpi-grid" style="margin-bottom:1.5rem;">
    <div class="kpi-card"><div class="kpi-icon blue"><i data-lucide="ticket"></i></div><div class="kpi-body"><div class="kpi-label">Total</div><div class="kpi-value">{{ $stats['total'] ?? 0 }}</div></div></div>
    <div class="kpi-card"><div class="kpi-icon" style="background:rgba(239,68,68,.1);"><i data-lucide="alert-circle" style="color:var(--p-danger);"></i></div><div class="kpi-body"><div class="kpi-label">Open</div><div class="kpi-value" style="color:var(--p-danger);">{{ $stats['open'] ?? 0 }}</div></div></div>
    <div class="kpi-card"><div class="kpi-icon" style="background:rgba(245,158,11,.1);"><i data-lucide="clock" style="color:var(--p-warning);"></i></div><div class="kpi-body"><div class="kpi-label">Pending</div><div class="kpi-value" style="color:var(--p-warning);">{{ $stats['pending'] ?? 0 }}</div></div></div>
    <div class="kpi-card"><div class="kpi-icon" style="background:rgba(16,185,129,.1);"><i data-lucide="check-circle" style="color:#10b981;"></i></div><div class="kpi-body"><div class="kpi-label">Resolved</div><div class="kpi-value" style="color:#10b981;">{{ $stats['resolved'] ?? 0 }}</div></div></div>
</div>

<form method="GET" action="{{ route('portals.admin.support.index') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach(['open','pending','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Priority</label>
            <select name="priority" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach(['low','medium','high','urgent'] as $p)
                <option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>
                @endforeach
            </select>
        </div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Category</label>
            <select name="category" class="form-control form-control-sm">
                <option value="">All</option>
                @foreach($categories ?? [] as $cat)
                <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ ucfirst($cat) }}</option>
                @endforeach
            </select>
        </div>
        <div style="flex:1;min-width:180px;"><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Ticket # or subject…" value="{{ request('search') }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('portals.admin.support.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>

<div class="panel">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Subject</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Assignee</th>
                    <th>Created</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets ?? [] as $ticket)
                @php
                $statusBadge=match($ticket->status??'open'){'open'=>'badge-danger','pending'=>'badge-warning','resolved'=>'badge-success','closed'=>'badge-neutral',default=>'badge-neutral'};
                $prioBadge=match($ticket->priority??'medium'){'urgent'=>'badge-danger','high'=>'badge-warning','medium'=>'badge-primary','low'=>'badge-neutral',default=>'badge-neutral'};
                @endphp
                <tr>
                    <td><span style="font-weight:600;color:var(--p-text-muted);">#{{ $ticket->ticket_number ?? $ticket->id }}</span></td>
                    <td><a href="{{ route('portals.admin.support.show', $ticket) }}" style="font-weight:600;text-decoration:none;color:var(--p-text);">{{ Str::limit($ticket->subject, 55) }}</a></td>
                    <td><span class="badge badge-neutral">{{ ucfirst($ticket->category ?? 'General') }}</span></td>
                    <td><span class="badge {{ $prioBadge }}">{{ ucfirst($ticket->priority ?? 'Medium') }}</span></td>
                    <td><span class="badge {{ $statusBadge }}">{{ ucfirst($ticket->status ?? 'Open') }}</span></td>
                    <td style="font-size:.82rem;">{{ $ticket->assignee?->name ?? '—' }}</td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);">{{ $ticket->created_at?->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:.35rem;justify-content:flex-end;">
                            <a href="{{ route('portals.admin.support.show', $ticket) }}" class="btn btn-primary btn-xs"><i data-lucide="eye"></i></a>
                            @if(!in_array($ticket->status??'',['closed','resolved']))
                            <form method="POST" action="{{ route('portals.admin.support.close', $ticket) }}" onsubmit="return confirm('Close this ticket?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-danger btn-xs"><i data-lucide="x-circle"></i></button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($tickets) && $tickets->hasPages())
    <div style="padding:.75rem 1.25rem;">{{ $tickets->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
