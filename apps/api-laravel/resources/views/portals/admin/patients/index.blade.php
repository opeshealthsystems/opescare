@extends('layouts.portal')
@section('title', 'All Patients')
@include('portals.admin.control_center._sidebar')
@section('breadcrumb_home', 'Admin')
@section('breadcrumb_home_url', route('portals.admin'))
@section('breadcrumb_section', 'Patients')
@section('content')
<div class="page-header"><div>
    <h1 class="page-title">All Patients</h1>
    <p class="page-subtitle">Master patient registry across all facilities.</p>
</div></div>
@if(session('success'))<div class="auth-alert auth-alert-success" style="margin-bottom:1rem;"><i data-lucide="check-circle"></i><div>{{ session('success') }}</div></div>@endif
@if(session('error'))<div class="auth-alert auth-alert-danger" style="margin-bottom:1rem;"><i data-lucide="alert-circle"></i><div>{{ session('error') }}</div></div>@endif
<form method="GET" action="{{ route('admin.patients.index') }}" class="panel" style="padding:1rem;margin-bottom:1rem;">
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <div style="flex:1;min-width:200px;"><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Name, Health ID, or phone" class="form-control form-control-sm"></div>
        <div><label style="font-size:.8rem;color:var(--p-text-muted);display:block;margin-bottom:.2rem;">Identity Status</label>
            <select name="identity_status" class="form-control form-control-sm"><option value="">All</option>@foreach(['provisional','verified','flagged','deceased'] as $s)<option value="{{ $s }}" {{ request('identity_status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>@endforeach</select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('admin.patients.index') }}" class="btn btn-ghost btn-sm">Reset</a>
    </div>
</form>
<div class="panel">
    <div style="padding:.75rem 1.25rem;border-bottom:1px solid var(--p-border);"><span style="font-size:.85rem;color:var(--p-text-muted);">{{ $patients->total() }} patients</span></div>
    <div class="table-wrapper"><table class="data-table"><thead><tr><th>Health ID</th><th>Name</th><th>DOB</th><th>Sex</th><th>Identity</th><th>Created</th><th>Actions</th></tr></thead><tbody>
    @forelse($patients as $patient)
    <tr>
        <td><code style="font-size:.78rem;">{{ $patient->health_id }}</code></td>
        <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
        <td style="font-size:.8rem;">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->format('d M Y') : '—' }}</td>
        <td>{{ ucfirst($patient->sex??'') }}</td>
        <td>@php $ist=$patient->identity_status??'provisional'; @endphp
            @if($ist==='verified')<span class="badge badge-success">Verified</span>@elseif($ist==='flagged')<span class="badge badge-danger">Flagged</span>@elseif($ist==='deceased')<span class="badge" style="background:var(--p-surface-3);color:var(--p-text-muted);">Deceased</span>@else<span class="badge badge-warning">Provisional</span>@endif
        </td>
        <td style="font-size:.8rem;">{{ $patient->created_at?->format('d M Y') }}</td>
        <td><div style="display:flex;gap:.35rem;">
            @if($ist!=='verified')<form method="POST" action="{{ route('admin.patients.activate',$patient->id) }}">@csrf<button class="btn btn-success btn-xs">Verify</button></form>@endif
            @if($ist!=='flagged')<form method="POST" action="{{ route('admin.patients.suspend',$patient->id) }}">@csrf<button class="btn btn-warning btn-xs">Flag</button></form>@endif
            <form method="POST" action="{{ route('admin.patients.destroy',$patient->id) }}" onsubmit="return confirm('Delete patient?')">@csrf @method('DELETE')<button class="btn btn-danger btn-xs">Delete</button></form>
        </div></td>
    </tr>
    @empty<tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--p-text-muted);">No patients found.</td></tr>@endforelse
    </tbody></table></div>
    <div style="padding:.75rem 1.25rem;">{{ $patients->links() }}</div>
</div>
@endsection