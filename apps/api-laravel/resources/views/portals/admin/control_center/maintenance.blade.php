@extends('layouts.portal')
@section('title', 'Maintenance Windows')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin Portal')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Maintenance')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Maintenance Windows</h1>
        <p class="page-subtitle">Schedule and manage platform downtime windows.</p>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
            <i data-lucide="plus" style="width:14px;height:14px;"></i> Schedule Maintenance
        </button>
    </div>
</div>

@if(session('success'))
    <div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if(session('error'))
    <div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="triangle-alert"></i><div>{{ session('error') }}</div></div>
@endif

{{-- Active Window Banner --}}
@php $active = $windows->firstWhere('is_active', true); @endphp
@if($active && $active->isCurrentlyActive())
<div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:var(--p-radius);padding:1rem;margin-bottom:1rem;display:flex;gap:.75rem;align-items:center;">
    <i data-lucide="wrench" style="width:18px;height:18px;color:var(--p-danger);flex-shrink:0;"></i>
    <div>
        <strong>Maintenance in progress:</strong> {{ $active->title }}
        <span style="font-size:.8rem;color:var(--p-text-muted);margin-left:.5rem;">
            Until {{ \Carbon\Carbon::parse($active->ends_at)->format('M d, Y H:i') }}
        </span>
    </div>
</div>
@endif

<div class="panel">
    <div class="panel-body" style="padding:0;">
        @if($windows->count() === 0)
            <div class="empty-state">
                <div class="empty-state-icon"><i data-lucide="wrench"></i></div>
                <h3>No maintenance windows</h3>
                <p>Schedule a maintenance window to notify users of planned downtime.</p>
            </div>
        @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead><tr>
                    <th>Title</th><th>Starts</th><th>Ends</th><th>Status</th><th>Emergency Access</th><th>Created By</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @foreach($windows as $win)
                    @php
                        $now = now();
                        $starts = \Carbon\Carbon::parse($win->starts_at);
                        $ends   = \Carbon\Carbon::parse($win->ends_at);
                        $live   = $win->is_active && $now->between($starts, $ends);
                        $upcoming = $win->is_active && $now->lt($starts);
                    @endphp
                    <tr>
                        <td style="font-weight:500;">
                            {{ $win->title }}
                            @if($win->message)
                                <div style="font-size:.75rem;color:var(--p-text-muted);margin-top:2px;">{{ Str::limit($win->message, 60) }}</div>
                            @endif
                        </td>
                        <td style="font-size:.82rem;">{{ $starts->format('M d, Y H:i') }}</td>
                        <td style="font-size:.82rem;">{{ $ends->format('M d, Y H:i') }}</td>
                        <td>
                            @if($live)
                                <span class="badge badge-danger">Live</span>
                            @elseif($upcoming)
                                <span class="badge badge-warning">Upcoming</span>
                            @elseif(!$win->is_active)
                                <span class="badge badge-neutral">Inactive</span>
                            @else
                                <span class="badge badge-neutral">Expired</span>
                            @endif
                        </td>
                        <td>
                            @if($win->allow_emergency_access)
                                <span class="badge badge-success" style="font-size:.72rem;">Allowed</span>
                            @else
                                <span class="badge badge-neutral" style="font-size:.72rem;">Blocked</span>
                            @endif
                        </td>
                        <td style="font-size:.78rem;color:var(--p-text-muted);">{{ $win->created_by ?? '—' }}</td>
                        <td>
                            <form method="POST" action="{{ route('portals.admin.cc.maintenance.toggle', $win->id) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="active" value="{{ $win->is_active ? '0' : '1' }}">
                                <button type="submit" class="btn {{ $win->is_active ? 'btn-ghost' : 'btn-success' }} btn-xs">
                                    {{ $win->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Create Modal --}}
<div id="create-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--p-surface);border-radius:var(--p-radius-lg);padding:2rem;width:100%;max-width:480px;margin:1rem;max-height:90vh;overflow-y:auto;">
        <h3 style="margin:0 0 1rem;font-size:1.05rem;">Schedule Maintenance Window</h3>
        <form method="POST" action="{{ route('portals.admin.cc.maintenance.store') }}">
            @csrf
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Title *</label>
                <input type="text" name="title" class="form-control" required maxlength="150" placeholder="e.g. Database migration v2.1">
            </div>
            <div class="form-group" style="margin-bottom:.75rem;">
                <label class="form-label">Message <span style="color:var(--p-text-muted);font-weight:400;">(shown to users)</span></label>
                <textarea name="message" class="form-control" rows="2" maxlength="500" placeholder="e.g. The platform will be temporarily unavailable for scheduled maintenance."></textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="datetime-local" name="starts_at" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Time *</label>
                    <input type="datetime-local" name="ends_at" class="form-control" required>
                </div>
            </div>
            <div class="form-group" style="margin-bottom:1rem;">
                <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer;font-size:.875rem;">
                    <input type="checkbox" name="allow_emergency_access" value="1" style="width:15px;height:15px;">
                    Allow emergency access during maintenance
                </label>
            </div>
            <div style="display:flex;gap:.5rem;justify-content:flex-end;">
                <button type="button" class="btn btn-ghost btn-sm" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary btn-sm">Schedule</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openCreateModal()  { document.getElementById('create-modal').style.display = 'flex'; }
    function closeCreateModal() { document.getElementById('create-modal').style.display = 'none'; }
    document.getElementById('create-modal').addEventListener('click', function(e) { if (e.target === this) closeCreateModal(); });
</script>
@endsection
