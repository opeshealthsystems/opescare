@extends('layouts.portal')
@section('title', 'Role Users — ' . $role->name)
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles')
@section('content')

<div class="breadcrumb">
    <a href="{{ route('portals.admin.roles.index') }}">Roles</a>
    <i data-lucide="chevron-right"></i>
    <span>{{ $role->name }}</span>
</div>

<div class="entity-head">
    <div class="entity-head__icon"><i data-lucide="shield"></i></div>
    <h2 class="entity-head__title">{{ $role->name }}</h2>
    <span class="badge badge-primary">{{ ucfirst(str_replace('_',' ',$role->portal??'—')) }}</span>
    @if($role->is_protected)<span class="badge badge-warning">Protected</span>@endif
    <div class="entity-head__spacer"></div>
</div>

<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="stat-card__label">Total Users</div>
        <div class="stat-card__value">{{ $users->total() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Portal</div>
        <div class="stat-card__value">{{ ucfirst(str_replace('_',' ',$role->portal??'—')) }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__label">Protected</div>
        <div class="stat-card__value">{{ $role->is_protected ? 'Yes' : 'No' }}</div>
    </div>
</div>

<div class="panel">
    <div class="panel-header"><h3 class="panel-title"><i data-lucide="users"></i> Users with this Role</h3></div>
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
                    <td data-label="Name"><span class="td-strong">{{ $user->name ?? '—' }}</span></td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Facility" class="td-muted">{{ $user->facility?->name ?? '—' }}</td>
                    <td data-label="Status">
                        @if($user->is_active ?? true)<span class="badge badge-success">Active</span>
                        @else<span class="badge badge-neutral">Inactive</span>@endif
                    </td>
                    <td data-label="Joined" class="td-muted">{{ $user->created_at?->format('d M Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="td-muted empty-cell">No users assigned to this role.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="panel-body">{{ $users->links() }}</div>
</div>
@endsection
