@extends('layouts.portal')
@section('title', 'Ticket #' . ($ticket->ticket_number ?? $ticket->id))
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Support')
@section('content')

@php
$statusBadge=match($ticket->status??'open'){'open'=>'badge-danger','pending'=>'badge-warning','resolved'=>'badge-success','closed'=>'badge-neutral',default=>'badge-neutral'};
$prioBadge=match($ticket->priority??'medium'){'urgent'=>'badge-danger','high'=>'badge-warning','medium'=>'badge-primary','low'=>'badge-neutral',default=>'badge-neutral'};
@endphp

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
    <div>
        <a href="{{ route('portals.admin.support.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Support Tickets
        </a>
        <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;">
            <span style="font-size:.85rem;color:var(--p-text-muted);font-weight:600;">#{{ $ticket->ticket_number ?? $ticket->id }}</span>
            <span class="badge {{ $statusBadge }}">{{ ucfirst($ticket->status ?? 'Open') }}</span>
            <span class="badge {{ $prioBadge }}">{{ ucfirst($ticket->priority ?? 'Medium') }} Priority</span>
        </div>
        <h1 class="page-title">{{ $ticket->subject }}</h1>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
        @if(!in_array($ticket->status??'',['closed','resolved']))
        <form method="POST" action="{{ route('portals.admin.support.close', $ticket) }}" onsubmit="return confirm('Close this ticket?')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-warning btn-sm"><i data-lucide="x-circle"></i> Close</button>
        </form>
        @endif
        @if(in_array($ticket->status??'',['closed','resolved']))
        <form method="POST" action="{{ route('portals.admin.support.reopen', $ticket) }}" onsubmit="return confirm('Reopen?')">
            @csrf @method('PATCH')
            <button type="submit" class="btn btn-success btn-sm"><i data-lucide="refresh-cw"></i> Reopen</button>
        </form>
        @endif
        <form method="POST" action="{{ route('portals.admin.support.destroy', $ticket) }}" onsubmit="return confirm('Permanently delete this ticket?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm"><i data-lucide="trash-2"></i> Delete</button>
        </form>
    </div>
</div>

@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">
    <div>
        <div class="panel" style="margin-bottom:1.5rem;">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="message-square"></i> Description</h2></div>
            <div style="padding:1.25rem;white-space:pre-wrap;line-height:1.7;font-size:.9rem;">{{ $ticket->description ?? $ticket->body ?? 'No description provided.' }}</div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="messages-square"></i> Replies @if(isset($ticket->replies))<span style="font-size:.8rem;font-weight:400;color:var(--p-text-muted);">({{ $ticket->replies->count() }})</span>@endif</h2></div>
            @if(isset($ticket->replies) && $ticket->replies->count())
            @foreach($ticket->replies as $reply)
            <div style="padding:1rem 1.25rem;{{ !$loop->last ? 'border-bottom:1px solid var(--p-border);' : '' }}">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.5rem;">
                    <div style="display:flex;align-items:center;gap:.6rem;">
                        <div style="width:28px;height:28px;border-radius:50%;background:var(--p-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.7rem;font-weight:700;">
                            {{ strtoupper(substr($reply->author?->name ?? 'S', 0, 1)) }}
                        </div>
                        <span style="font-weight:600;font-size:.875rem;">{{ $reply->author?->name ?? 'Staff' }}</span>
                        @if($reply->is_staff_reply ?? false)<span style="font-size:.65rem;background:rgba(14,165,233,.1);color:var(--p-primary);padding:.1rem .4rem;border-radius:3px;margin-left:.3rem;">Staff</span>@endif
                    </div>
                    <span style="font-size:.78rem;color:var(--p-text-muted);">{{ $reply->created_at?->format('d M Y, H:i') }}</span>
                </div>
                <div style="padding-left:2rem;font-size:.875rem;line-height:1.6;white-space:pre-wrap;">{{ $reply->body ?? $reply->message }}</div>
            </div>
            @endforeach
            @else
            <div style="padding:2rem;text-align:center;color:var(--p-text-muted);font-size:.875rem;">No replies yet.</div>
            @endif
        </div>
    </div>

    <div>
        <div class="panel" style="margin-bottom:1rem;">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="info"></i> Ticket Details</h2></div>
            <div style="padding:1.25rem;">
                <dl style="display:grid;grid-template-columns:auto 1fr;gap:.5rem .75rem;font-size:.85rem;">
                    <dt style="color:var(--p-text-muted);">Status</dt>
                    <dd style="margin:0;"><span class="badge {{ $statusBadge }}">{{ ucfirst($ticket->status ?? 'Open') }}</span></dd>
                    <dt style="color:var(--p-text-muted);">Priority</dt>
                    <dd style="margin:0;"><span class="badge {{ $prioBadge }}">{{ ucfirst($ticket->priority ?? 'Medium') }}</span></dd>
                    <dt style="color:var(--p-text-muted);">Category</dt>
                    <dd style="margin:0;">{{ ucfirst($ticket->category ?? 'General') }}</dd>
                    <dt style="color:var(--p-text-muted);">Submitted by</dt>
                    <dd style="margin:0;">{{ $ticket->submittedBy?->name ?? $ticket->user?->name ?? 'Unknown' }}</dd>
                    <dt style="color:var(--p-text-muted);">Assignee</dt>
                    <dd style="margin:0;">{{ $ticket->assignee?->name ?? 'Unassigned' }}</dd>
                    <dt style="color:var(--p-text-muted);">Created</dt>
                    <dd style="margin:0;">{{ $ticket->created_at?->format('d M Y') }}</dd>
                    @if($ticket->resolved_at)
                    <dt style="color:var(--p-text-muted);">Resolved</dt>
                    <dd style="margin:0;">{{ $ticket->resolved_at?->format('d M Y') }}</dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="panel">
            <div class="panel-header"><h2 class="panel-title"><i data-lucide="user-check"></i> Assign</h2></div>
            <div style="padding:1.25rem;">
                <form method="POST" action="{{ route('portals.admin.support.assign', $ticket) }}">
                    @csrf @method('PATCH')
                    <div class="form-group" style="margin-bottom:1rem;">
                        <label class="form-label">Assign To</label>
                        <select name="assignee_id" class="form-control">
                            <option value="">— Unassigned —</option>
                            @foreach($staffUsers ?? [] as $staff)
                            <option value="{{ $staff->id }}" @selected(($ticket->assignee_id??null)==$staff->id)>{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm" style="width:100%;">
                        <i data-lucide="check"></i> Update Assignee
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
