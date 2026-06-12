@extends('layouts.portal')
@section('title', 'Organizations')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Organizations')
@section('content')
<div class="page-header"><div>
    <h1 class="page-title">Organizations</h1>
    <p class="page-subtitle">All registered organizations and their approval status.</p>
</div></div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
@if($pendingCount > 0)
<div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);border-radius:var(--p-radius);padding:1rem 1.25rem;margin-bottom:1rem;">
    <strong>{{ $pendingCount }} organization{{ $pendingCount>1?'s':'' }} awaiting approval</strong>
</div>
@foreach($pending as $p)
<div class="panel" style="margin-bottom:.5rem;padding:1rem 1.25rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
    <div><strong>{{ $p->name }}</strong> <span style="font-size:.8rem;color:var(--p-text-muted);">{{ ucfirst($p->type??'') }}</span>
        <div style="font-size:.78rem;color:var(--p-text-muted);">License: {{ $p->license_number??'—' }}</div>
    </div>
    <div style="display:flex;gap:.5rem;">
        <form method="POST" action="{{ route('admin.organizations.approve',$p->id) }}">@csrf<button class="btn btn-success btn-sm">Approve</button></form>
        <form method="POST" action="{{ route('admin.organizations.reject',$p->id) }}" onsubmit="return confirm('Reject?')">@csrf<button class="btn btn-danger btn-sm">Reject</button></form>
    </div>
</div>
@endforeach
@endif
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);display:flex;gap:1.5rem;flex-wrap:wrap;">
        <span style="font-size:.85rem;color:var(--p-text-muted);">Total: {{ $total }}</span>
        @foreach($byType as $type => $count)<span style="font-size:.82rem;"><strong>{{ $count }}</strong> {{ $type }}</span>@endforeach
    </div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Name</th><th>Type</th><th>License</th><th>Status</th><th>Since</th><th>Actions</th></tr></thead><tbody>
    @forelse($facilities as $f)
    <tr>
        <td><strong>{{ $f->name }}</strong></td><td>{{ ucfirst($f->type??'') }}</td>
        <td style="font-size:.78rem;font-family:monospace;">{{ $f->license_number??'—' }}</td>
        <td>@if($f->status==='active')<span class="badge badge-success">Active</span>@elseif(in_array($f->status,['suspended','rejected']))<span class="badge badge-danger">{{ ucfirst($f->status) }}</span>@else<span class="badge badge-warning">{{ ucfirst($f->status??'pending') }}</span>@endif</td>
        <td style="font-size:.8rem;">{{ $f->created_at?->format('d M Y') }}</td>
        <td><div style="display:flex;gap:.35rem;">
            @if($f->status==='pending')<form method="POST" action="{{ route('admin.organizations.approve',$f->id) }}">@csrf<button class="btn btn-success btn-xs">Approve</button></form>@endif
            <form method="POST" action="{{ route('admin.organizations.destroy',$f->id) }}" onsubmit="return confirm('Delete organization?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
        </div></td>
    </tr>
    @empty<tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No organizations found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $facilities->links() }}</div>
</div>
@endsection