@extends('layouts.portal')
@section('title', 'Role Users')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Roles')
@section('content')
<div class="page-header">
    <div><h1 class="page-title">Users with role: <code>{{ $role->name }}</code></h1><p class="page-subtitle">{{ $users->total() }} users assigned.</p></div>
    <a href="{{ route('admin.roles.index') }}" class="btn btn-ghost btn-sm">Back to Roles</a>
</div>
<div class="panel">
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Since</th></tr></thead><tbody>
    @forelse($users as $user)
    <tr>
        <td>{{ $user->name }}</td><td>{{ $user->email }}</td>
        <td>@if($user->status==='active')<span class="badge badge-success">Active</span>@elseif($user->status==='suspended')<span class="badge badge-danger">Suspended</span>@else<span class="badge badge-warning">{{ ucfirst($user->status) }}</span>@endif</td>
        <td style="font-size:.8rem;">{{ $user->created_at?->format('d M Y') }}</td>
    </tr>
    @empty<tr><td colspan="4" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No users found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $users->links() }}</div>
</div>
@endsection