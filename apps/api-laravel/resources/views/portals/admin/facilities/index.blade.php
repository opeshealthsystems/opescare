@extends('layouts.portal')
@section('title', 'All Facilities')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Facilities')
@section('content')
<div class="page-header"><div>
    <h1 class="page-title">All Facilities</h1>
    <p class="page-subtitle">View and manage every facility registered on the platform.</p>
</div></div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
<form method="GET" action="{{ route('admin.facilities.index') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:180px;"><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Name or license" class="form-control form-control-sm"></div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Type</label>
            <select name="type" class="form-control form-control-sm"><option value="">All</option>@foreach(['hospital','clinic','laboratory','pharmacy','radiology','specialist'] as $t)<option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst($t) }}</option>@endforeach</select>
        </div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Status</label>
            <select name="status" class="form-control form-control-sm"><option value="">All</option><option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option><option value="pending" {{ request('status')==='pending'?'selected':'' }}>Pending</option><option value="suspended" {{ request('status')==='suspended'?'selected':'' }}>Suspended</option></select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.facilities.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);"><span style="font-size:.85rem;color:var(--p-text-muted);">{{ $facilities->total() }} facilities</span></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Name</th><th>Type</th><th>License</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead><tbody>
    @forelse($facilities as $f)
    <tr>
        <td><strong>{{ $f->name }}</strong></td><td>{{ ucfirst($f->type??'') }}</td>
        <td style="font-size:.78rem;font-family:monospace;">{{ $f->license_number??'—' }}</td>
        <td>@if($f->status==='active')<span class="badge badge-success">Active</span>@elseif($f->status==='suspended')<span class="badge badge-danger">Suspended</span>@else<span class="badge badge-warning">{{ ucfirst($f->status??'pending') }}</span>@endif</td>
        <td style="font-size:.8rem;">{{ $f->created_at?->format('d M Y') }}</td>
        <td><div style="display:flex;gap:.35rem;">
            @if(($f->status??'')!=='active')<form method="POST" action="{{ route('admin.facilities.approve',$f->id) }}">@csrf<button class="btn btn-success btn-xs">Approve</button></form>@endif
            @if(($f->status??'')!=='suspended')<form method="POST" action="{{ route('admin.facilities.suspend',$f->id) }}">@csrf<button class="btn btn-warning btn-xs">Suspend</button></form>@endif
            <form method="POST" action="{{ route('admin.facilities.destroy',$f->id) }}" onsubmit="return confirm('Delete facility?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
        </div></td>
    </tr>
    @empty<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No facilities found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $facilities->links() }}</div>
</div>
@endsection