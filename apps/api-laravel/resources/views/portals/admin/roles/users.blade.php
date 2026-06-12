@extends('layouts.portal')
@section('title', 'Role Users — ' . $role->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles')
@section('content')

<div class="page-header">
    <div>
        <a href="{{ route('portals.admin.roles.index') }}" style="font-size:.82rem;color:var(--p-text-muted);display:inline-flex;align-items:center;gap:.3rem;margin-bottom:.4rem;">
            <i data-lucide="arrow-left" style="width:13px;height:13px;"></i> Roles
        </a>
        <h1 class="page-title">{{ $role->name }}</h1>
        <p class="page-subtitle">{{ $role->description ?? 'Users assigned to this role.' }}</p>
    </div>
</div>

<div class="kpi-grid" style="margin-bottom:1.5rem;">
    <div class="kpi-card">
        <div class="kpi-icon blue"><i data-lucide="users"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Total Users</div>
            <div class="kpi-value">{{ $users->total() }}</div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon teal"><i data-lucide="shield"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Portal</div>
            <div class="kpi-value" style="font-size:1rem;margin-top:.25rem;">
                <span class="badge badge-primary">{{ ucfirst(str_replace('_',' ',$role->portal??'—')) }}</span>
            </div>
        </div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon purple"><i data-lucide="lock"></i></div>
        <div class="kpi-body">
            <div class="kpi-label">Protected</div>
            <div class="kpi-value" style="font-size:1rem;margin-top:.25rem;">
                @if($role->is_protected)<span class="badge badge-warning">Yes</span>@else<span class="badge badge-neutral">No</span>@endif
            </div>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h2 class="panel-title"><i data-lucide="users"></i> Users with this Role</h2></div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Facility</th>
                    <th>Status</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:.6rem;">
                            <div style="width:32px;height:32px;min-width:32px;border-radius:50%;background:var(--p-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700;">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>
                            <span style="font-weight:600;">{{ $user->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td style="font-size:.85rem;">{{ $user->email }}</td>
                    <td style="font-size:.85rem;">{{ $user->facility?->name ?? '—' }}</td>
                    <td>
                        @if($user->is_active ?? true)<span class="badge badge-success">Active</span>
                        @else<span class="badge badge-neutral">Inactive</span>@endif
                    </td>
                    <td style="font-size:.82rem;color:var(--p-text-muted);">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No users assigned to this role.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:.75rem 1.25rem;">{{ $users->links() }}</div>
</div>
@endsection
