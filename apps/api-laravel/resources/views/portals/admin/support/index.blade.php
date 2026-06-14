@extends('layouts.portal')
@section('title', 'Support Tickets')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Support')
@section('content')

<div class="page-head">
    <h2>Support Tickets</h2>
    <div class="page-head__spacer"></div>
</div>
<p class="td-muted mb-6">Manage and resolve platform support requests.</p>

@if(session('success'))<div class="alert alert-success mb-6"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="alert alert-danger mb-6"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif

<div class="stat-grid mb-6">
    <div class="stat-card stat-card--primary"><div class="stat-card__head"><i data-lucide="ticket"></i></div><div class="stat-card__label">Total</div><div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div></div>
    <div class="stat-card stat-card--danger"><div class="stat-card__head"><i data-lucide="alert-circle"></i></div><div class="stat-card__label">Open</div><div class="stat-card__value">{{ $stats['open'] ?? 0 }}</div></div>
    <div class="stat-card stat-card--warning"><div class="stat-card__head"><i data-lucide="clock"></i></div><div class="stat-card__label">Pending</div><div class="stat-card__value">{{ $stats['pending'] ?? 0 }}</div></div>
    <div class="stat-card stat-card--success"><div class="stat-card__head"><i data-lucide="check-circle"></i></div><div class="stat-card__label">Resolved</div><div class="stat-card__value">{{ $stats['resolved'] ?? 0 }}</div></div>
</div>

<form method="GET" action="{{ route('portals.admin.support.index') }}" class="filter-bar">
    <select name="status" class="filter-select" aria-label="Status">
        <option value="">All statuses</option>
        @foreach(['open','pending','resolved','closed'] as $s)
        <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst($s) }}</option>
        @endforeach
    </select>
    <select name="priority" class="filter-select" aria-label="Priority">
        <option value="">All priorities</option>
        @foreach(['low','medium','high','urgent'] as $p)
        <option value="{{ $p }}" @selected(request('priority')===$p)>{{ ucfirst($p) }}</option>
        @endforeach
    </select>
    <select name="category" class="filter-select" aria-label="Category">
        <option value="">All categories</option>
        @foreach($categories ?? [] as $cat)
        <option value="{{ $cat }}" @selected(request('category')===$cat)>{{ ucfirst($cat) }}</option>
        @endforeach
    </select>
    <label class="filter-search">
        <i data-lucide="search"></i>
        <input type="text" name="search" placeholder="Ticket # or subject…" value="{{ request('search') }}" aria-label="Search">
    </label>
    <button type="submit" class="btn btn-primary btn-sm"><i data-lucide="filter"></i> Filter</button>
    <a href="{{ route('portals.admin.support.index') }}" class="btn btn-ghost btn-sm">Reset</a>
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
                    <th class="row-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets ?? [] as $ticket)
                @php
                $statusBadge=match($ticket->status??'open'){'open'=>'badge-danger','pending'=>'badge-warning','resolved'=>'badge-success','closed'=>'badge-neutral',default=>'badge-neutral'};
                $prioBadge=match($ticket->priority??'medium'){'urgent'=>'badge-danger','high'=>'badge-warning','medium'=>'badge-primary','low'=>'badge-neutral',default=>'badge-neutral'};
                @endphp
                <tr>
                    <td data-label="Ticket #"><span class="td-muted td-strong">#{{ $ticket->ticket_number ?? $ticket->id }}</span></td>
                    <td data-label="Subject"><a href="{{ route('portals.admin.support.show', $ticket) }}" class="td-strong">{{ Str::limit($ticket->subject, 55) }}</a></td>
                    <td data-label="Category"><span class="badge badge-neutral">{{ ucfirst($ticket->category ?? 'General') }}</span></td>
                    <td data-label="Priority"><span class="badge {{ $prioBadge }}">{{ ucfirst($ticket->priority ?? 'Medium') }}</span></td>
                    <td data-label="Status"><span class="badge {{ $statusBadge }}">{{ ucfirst($ticket->status ?? 'Open') }}</span></td>
                    <td data-label="Assignee">{{ $ticket->assignee?->name ?? '—' }}</td>
                    <td data-label="Created">{{ $ticket->created_at?->format('d M Y') }}</td>
                    <td class="row-actions" data-label="Actions">
                        <a href="{{ route('portals.admin.support.show', $ticket) }}" class="icon-btn" aria-label="View ticket" title="View"><i data-lucide="eye"></i></a>
                        @if(!in_array($ticket->status??'',['closed','resolved']))
                        <button type="button" class="btn btn-danger btn-sm" title="Close" onclick="opOpenModal('close-{{ $ticket->id }}')"><i data-lucide="x-circle"></i></button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="td-muted empty-cell">No tickets found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(isset($tickets) && $tickets->hasPages())
    <div class="panel-body">{{ $tickets->withQueryString()->links() }}</div>
    @endif
</div>

{{-- Close confirm modals --}}
@foreach($tickets ?? [] as $ticket)
    @if(!in_array($ticket->status??'',['closed','resolved']))
    <div id="close-{{ $ticket->id }}" class="modal-backdrop mt-6" hidden>
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="close-{{ $ticket->id }}-title">
            <h3 class="modal__title" id="close-{{ $ticket->id }}-title"><i data-lucide="x-circle"></i> Close ticket</h3>
            <form method="POST" action="{{ route('portals.admin.support.close', $ticket) }}">
                @csrf @method('PATCH')
                <div class="modal__body"><p>Close ticket <strong>#{{ $ticket->ticket_number ?? $ticket->id }}</strong>?</p></div>
                <div class="modal__footer">
                    <button type="button" class="btn btn-ghost" onclick="opCloseModal('close-{{ $ticket->id }}')">Cancel</button>
                    <button type="submit" class="btn btn-danger">Close</button>
                </div>
            </form>
        </div>
    </div>
    @endif
@endforeach

@endsection
@section('scripts')
<script>
function opOpenModal(id){ document.getElementById(id).removeAttribute('hidden'); }
function opCloseModal(id){ document.getElementById(id).setAttribute('hidden',''); }
document.addEventListener('keydown', function(e){
    if(e.key==='Escape'){ document.querySelectorAll('.modal-backdrop').forEach(function(m){ m.setAttribute('hidden',''); }); }
});
</script>
@endsection
