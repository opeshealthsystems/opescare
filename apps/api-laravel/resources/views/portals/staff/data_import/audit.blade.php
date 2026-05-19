@extends('layouts.portal')

@section('title', 'Import Audit Log')

@section('sidebar_role_badge')
<div class="sidebar-role-badge">Clinical Staff</div>
@endsection
@section('sidebar_user_role', 'Clinical Staff')

@section('sidebar_nav')
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Overview</div>
    <a href="{{ route('portals.staff') }}" class="sidebar-link"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
</div>
<div class="sidebar-nav-section">
    <div class="sidebar-nav-label">Operations</div>
    <a href="{{ route('portals.staff.data_import.index') }}" class="sidebar-link active"><i data-lucide="upload-cloud"></i><span>Data Import</span></a>
</div>
    <a href="{{ route('portals.staff.supply') }}" class="sidebar-link {{ request()->routeIs('portals.staff.supply*') ? 'active' : '' }}">
        <i data-lucide="package"></i> Supply Chain</a>
@endsection

@section('breadcrumb_home', 'Staff Portal')
@section('breadcrumb_home_url', route('portals.staff'))
@section('breadcrumb_section', 'Data Import')

@section('content')

<div style="max-width:680px;margin:0 auto;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <div>
            <h1 style="font-size:1.15rem;margin:0 0 .2rem;">Import Audit Log</h1>
            <p style="color:var(--p-text-muted);font-size:.83rem;margin:0;">
                {{ $job->original_filename }} · {{ $importTypes[$job->import_type]['label'] ?? $job->import_type }}
            </p>
        </div>
        <a href="{{ route('portals.staff.data_import.index') }}" class="btn btn-ghost btn-sm">← Back</a>
    </div>

    <div class="panel">
        <div class="panel-body" style="padding:0;">
            @if($job->auditEvents->count() === 0)
                <div class="empty-state">
                    <div class="empty-state-icon"><i data-lucide="scroll-text"></i></div>
                    <h3>No audit events</h3>
                    <p>No events have been recorded for this import job.</p>
                </div>
            @else
                <div style="padding:.75rem 1.25rem;">
                    @foreach($job->auditEvents->sortBy('occurred_at') as $event)
                    <div style="display:flex;gap:.85rem;padding:.75rem 0;border-bottom:1px solid var(--p-border);align-items:flex-start;">
                        <div style="flex-shrink:0;width:28px;height:28px;border-radius:50%;background:var(--p-surface-2,#f1f5f9);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="activity" style="width:13px;height:13px;color:var(--p-primary);"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:.5rem;flex-wrap:wrap;">
                                <span style="font-weight:600;font-size:.88rem;text-transform:capitalize;">{{ str_replace('_', ' ', $event->action) }}</span>
                                <span style="font-size:.75rem;color:var(--p-text-muted);">{{ \Carbon\Carbon::parse($event->occurred_at)->format('M d, Y H:i:s') }}</span>
                            </div>
                            @if($event->actor_id)
                                <div style="font-size:.78rem;color:var(--p-text-muted);margin-top:.15rem;">by {{ $event->actor_id }}</div>
                            @endif
                            @if($event->details)
                                <div style="margin-top:.4rem;font-size:.78rem;background:var(--p-surface-2,#f1f5f9);border-radius:var(--p-radius);padding:.4rem .6rem;font-family:monospace;overflow-x:auto;">
                                    {{ json_encode($event->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
