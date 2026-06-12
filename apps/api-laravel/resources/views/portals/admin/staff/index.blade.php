@extends('layouts.portal')
@section('title', 'All Staff')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Staff')
@section('content')
<div class="page-header"><div>
    <h1 class="page-title">All Staff</h1>
    <p class="page-subtitle">Clinical and administrative staff across all facilities.</p>
</div></div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
<form method="GET" action="{{ route('admin.staff.index') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;"><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Name or staff number" class="form-control form-control-sm"></div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm"><option value="">All</option><option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option><option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option><option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option></select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);"><span style="font-size:.85rem;color:var(--p-text-muted);">{{ $staff->total() }} staff members</span></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Staff #</th><th>Name</th><th>Specialty</th><th>Facility</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($staff as $s)
    <tr>
        <td><code style="font-size:.78rem;">{{ $s->staff_number??'—' }}</code></td>
        <td>{{ ($s->first_name??'').' '.($s->last_name??'') }}</td>
        <td style="font-size:.82rem;">{{ $s->specialty??$s->designation??'—' }}</td>
        <td style="font-size:.82rem;">{{ $s->facility?->name??'—' }}</td>
        <td>@if(($s->status??'')==='active')<span class="badge badge-success">Active</span>@elseif(($s->status??'')==='suspended')<span class="badge badge-danger">Suspended</span>@else<span class="badge badge-warning">{{ ucfirst($s->status??'inactive') }}</span>@endif</td>
        <td><div style="display:flex;gap:.35rem;">
            @if(($s->status??'')!=='active')<form method="POST" action="{{ route('admin.staff.activate',$s->id) }}">@csrf<button class="btn btn-success btn-xs">Activate</button></form>@endif
            @if(($s->status??'')!=='suspended')<form method="POST" action="{{ route('admin.staff.suspend',$s->id) }}">@csrf<button class="btn btn-warning btn-xs">Suspend</button></form>@endif
        </div></td>
    </tr>
    @empty<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No staff found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $staff->links() }}</div>
</div>
@endsection